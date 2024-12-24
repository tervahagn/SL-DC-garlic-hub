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
			if ($this->nodesRepository->isTransactionActive())
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

	private function hasRights(array $node)
	{
		return ($node['UID'] === $this->UID || $node['is_public'] === 1);
	}
}