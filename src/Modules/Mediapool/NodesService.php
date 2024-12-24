<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


namespace App\Modules\Mediapool;

use App\Framework\Exceptions\DatabaseException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use Doctrine\DBAL\Exception;


class NodesService
{
	private NodesRepository $nodesRepository;
	private int $UID;

	public function __construct(NodesRepository $nodesRepository)
	{
		$this->nodesRepository = $nodesRepository;
	}

	public function setUID(int $UID): void
	{
		$this->UID = $UID;
	}

	/**
	 * @throws Exception
	 */
	public function getNodes(int $parent_id): array
	{
		if ($parent_id === 0)
			$node_data = $this->nodesRepository->findAllRootNodes();
		else
			$node_data =  $this->nodesRepository->findAllChildNodesByParentNode($parent_id);

		$nodes = [];
		foreach ($node_data as $node)
		{
			if ($this->hasRights($node))
				$nodes[] = $this->prepareForWunderbaum($node);
		}

		return $nodes;
	}

	/**
	 * @throws ModuleException
	 * @throws Exception
	 */
	public function addNode(int $parent_id, string $name): int
	{
		if ($parent_id === 0)
			$new_node_id = $this->addRootNode($name);
		else
			$new_node_id =  $this->addSubNode($parent_id, $name);

		return $new_node_id;
	}

	/**
	 * @throws Exception
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	public function deleteNode($node_id): int
	{
		$node = $this->nodesRepository->getFirstDataSet($this->nodesRepository->getNode($node_id));
		if (empty($node) )
			throw new FrameworkException('Can not find a node for node_id ' . $node_id);

		if (!$this->hasRights($node))
			throw new FrameworkException('No rights to delete node ' . $node_id);

		// get all node_id of the partial tree
		$deleted_nodes = $this->nodesRepository->findAllSubNodeIdsByRootIdsAndPosition($node['root_id'], $node['rgt'], $node['lft']);

		if ($node['children'] == 0)
			$this->deleteSingleNode($node);
		elseif ($node['children'] > 0)
			$this->deleteTree($node);

		return count($deleted_nodes);
	}

	private function prepareForWunderbaum(array $node_data): array
	{
		return array(
			'title'         => $node_data['name'],
			'folder'        => true,
			'key'           => $node_data['node_id'],
			'lazy'          => ($node_data['children'] > 0),
			'create_sub'    => true,
			'edit_node'		=> true,
			'delete_node'	=> true,
			'UID'			=> $node_data['UID'],
			'is_public'		=> $node_data['is_public']
		);
	}

	/**
	 * @param $name
	 *
	 * @return int
	 * @throws Exception
	 * @throws ModuleException
	 */
	private function addRootNode($name): int
	{
		try
		{
			if ($this->UID != 1)
				throw new FrameworkException('No rights to add root node.');

			$node_data = array(
				'name'              => $name,
				'parent_id'         => 0,
				'root_order'        => 0,
				'is_public'		    => 0,
				'lft'               => 1,
				'rgt'               => 2,
				'UID'               => $this->UID,
				'level'             => 1
			);

			$this->nodesRepository->beginTransaction();
			$new_node_id = $this->nodesRepository->insert($node_data);

			if ($new_node_id == 0)
				throw new FrameworkException('Insert new node failed');

			$update_fields = array(
				'root_id'       => $new_node_id,
				'root_order'    => $new_node_id
			);

			if ($this->nodesRepository->update($new_node_id, $update_fields) === 0)
				throw new DatabaseException('Update root node failed');

			$this->nodesRepository->commitTransaction();

			return $new_node_id;
		}
		catch (\Exception | DatabaseException | FrameworkException $e)
		{
			$this->nodesRepository->rollbackTransaction();
			throw new ModuleException('mediapool', 'Add root node failed because of: '.$e->getMessage());
		}
	}

	/**
	 * @throws ModuleException
	 * @throws Exception
	 */
	public function addSubNode(int $parent_node_id, string $name): int
	{
		try
		{
			$parent_node = $this->nodesRepository->getFirstDataSet($this->nodesRepository->findById($parent_node_id));
			if ((empty($parent_node)))
				throw new ModuleException('mediapool', 'Parent node not found');

			if (!$this->hasRights($parent_node))
				throw new FrameworkException('No rights to add to node ' . $parent_node_id);

			$fields = array(
					'lft'       => $parent_node['rgt'],
					'rgt'       => $parent_node['rgt'] + 1,
					'parent_id' => $parent_node_id,
					'root_id'   => $parent_node['root_id'],
					'level'     => $parent_node['level'] + 1,
					'name'      => $name,
					'UID'       => $this->UID
			);

			$this->nodesRepository->moveNodesToLeftForInsert($parent_node['root_id'], $parent_node['rgt']);
			$this->nodesRepository->moveNodesToRightForInsert($parent_node['root_id'], $parent_node['rgt']);

			$new_node = $this->nodesRepository->insert($fields);
			if ($new_node == 0)
				throw new DatabaseException('Insert new sub node failed');

			$this->nodesRepository->commitTransaction();

			return $new_node;
		}
		catch (\Exception $e)
		{
			throw new ModuleException('mediapool', 'Add sub node failed because of: '.$e->getMessage());
		}
	}

	/**
	 * @throws ModuleException
	 * @throws Exception
	 */
	protected function deleteSingleNode(array $node_data): static
	{
		try
		{
			$this->nodesRepository->beginTransaction();

			$this->nodesRepository->delete($node_data['node_id']);
			$this->nodesRepository->moveNodesToLeftForDeletion($node_data['root_id'], $node_data['rgt']);
			$this->nodesRepository->moveNodesToRightForDeletion($node_data['root_id'], $node_data['rgt']);

			$this->nodesRepository->commitTransaction();
		}
		catch (Exception $e)
		{
			$this->nodesRepository->rollbackTransaction();
			throw new ModuleException('mediapool', 'delete single node failed because of: '.$e->getMessage());
		}
		return $this;
	}

	/**
	 * @throws ModuleException
	 * @throws Exception
	 */
	protected function deleteTree(array $node_data): static
	{
		$root_id = (int) $node_data['root_id'];
		$pos_rgt = (int) $node_data['rgt'];
		$pos_lft = (int) $node_data['lft'];

		try
		{
			$this->nodesRepository->beginTransaction();

			$this->nodesRepository->deleteFullTree($root_id, $pos_rgt, $pos_lft);

			// remove other nodes to to create some space
			$move = floor(($pos_rgt - $pos_lft) / 2);
			$move = 2 * (1 + $move);

			$this->nodesRepository->moveNodesToLeftForDeletionWithSteps($root_id, $pos_rgt, $move);
			$this->nodesRepository->moveNodesToRightForDeletionWithSteps($root_id, $pos_rgt, $move);

			$this->nodesRepository->commitTransaction();
		}
		catch (Exception $e)
		{
			$this->nodesRepository->rollbackTransaction();
			throw new ModuleException('mediapool', 'Delete tree failed because of: '.$e->getMessage());
		}


		return $this;
	}

	private function hasRights(array $node): bool
	{
		return ($node['UID'] === $this->UID || $node['is_public'] === 1);
	}
}