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

namespace App\Modules\Mediapool\Services;

use App\Framework\Database\BaseRepositories\NestedSetHelper;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\DatabaseException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\Repositories\NodesRepository;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;

class NodesService
{
	private readonly NodesRepository $nodesRepository;
	private readonly AclValidator $aclValidator;
	private int $UID;

	public function __construct(NodesRepository $nodesRepository, AclValidator $aclValidator)
	{
		$this->nodesRepository = $nodesRepository;
		$this->aclValidator    = $aclValidator;
	}

	public function setUID(int $UID): void
	{
		$this->UID = $UID;
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws Exception
	 */
	public function isModuleAdmin(int $UID): bool
	{
		return $this->aclValidator->isModuleAdmin($UID);
	}

	/**
	 * @throws CoreException
	 * @throws Exception
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function getNodes(int $parent_id): array
	{
		if ($parent_id === 0)
			$nodes = $this->nodesRepository->findAllRootNodes();
		else
			$nodes = $this->nodesRepository->findAllChildNodesByParentNode($parent_id);

		$tree = [];
		foreach ($nodes as $node)
		{
			$rights = $this->determineRights($node);
			if (!empty($rights))
				$tree[] = $this->prepareForWunderbaum($node, $rights);
		}

		return $tree;
	}

	/**
	 * @throws ModuleException
	 * @throws Exception
	 * @throws CoreException
	 * @throws DatabaseException|PhpfastcacheSimpleCacheException
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
	 * @throws ModuleException
	 * @throws Exception
	 * @throws CoreException|PhpfastcacheSimpleCacheException
	 */
	public function editNode(int $id, string $name, ?int $visibility = null): int
	{
		$node = $this->nodesRepository->getNode($id);
		if ((empty($node)))
			throw new ModuleException('mediapool', 'Parent node not found');

		$rights = $this->determineRights($node);
		if (!$rights['edit'])
			throw new ModuleException('mediapool', 'No rights to edit node ' . $node['name']);

		$data = ['name' => $name];
		if ($this->aclValidator->isModuleAdmin($this->UID) && !is_null($visibility))
			$data['visibility'] = $visibility;

		return $this->nodesRepository->update($id, $data);
	}

	/**
	 * @throws Exception
	 * @throws ModuleException
	 * @throws DatabaseException
	 * @throws FrameworkException
	 */
	public function moveNode(int $movedNodeId, int $targetNodeId, string $region): int
	{
		$regions = ['before', 'after', 'appendChild'];
		if (!in_array($region, $regions))
			throw new ModuleException('mediapool', $region.' is not supported');

		$movedNode  = $this->nodesRepository->getNode($movedNodeId);
		$targetNode = $this->nodesRepository->getNode($targetNodeId);

		// prevent root dir handling
		if ($movedNode['parent_id'] === 0)
			throw new ModuleException('mediapool', 'Moving root node is not allowed');

		if (($region === NestedSetHelper::REGION_APPENDCHILD && $targetNodeId === 0) ||
			(($region === NestedSetHelper::REGION_BEFORE || $region === NestedSetHelper::REGION_AFTER) &&
				$targetNode['parent_id'] === 0))
			throw new ModuleException('mediapool', 'Create root node with a move is not allowed');


		$this->nodesRepository->moveNode($movedNode, $targetNode, $region);

		return 1;
	}

	/**
	 * @throws Exception
	 * @throws ModuleException
	 * @throws DatabaseException
	 * @throws CoreException|PhpfastcacheSimpleCacheException
	 */
	public function deleteNode($node_id): int
	{
		$node = $this->nodesRepository->getNode($node_id);
		if (empty($node) )
			throw new ModuleException('mediapool', 'Can not find a node for node_id ' . $node_id);

		$rights = $this->determineRights($node);
		if (!$rights['delete'])
			throw new ModuleException('mediapool', 'No rights to delete node ' . $node_id);

		// get all node_id of the partial tree
		$deleted_nodes = $this->nodesRepository->findAllSubNodeIdsByRootIdsAndPosition($node['root_id'], $node['rgt'], $node['lft']);

		if ($node['children'] == 0)
			$this->nodesRepository->deleteSingleNode($node);
		elseif ($node['children'] > 0)
			$this->nodesRepository->deleteTree($node);

		return count($deleted_nodes);
	}

	private function prepareForWunderbaum(array $node, array $rights): array
	{
		return array(
			'title' => $node['name'],
			'folder' => true,
			'key' => $node['node_id'],
			'lazy' => ($node['children'] > 0),
			'rights' => $rights,
			'UID' => $node['UID'],
			'visibility' => $node['visibility']
		);
	}

	/**
	 * @throws Exception
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws DatabaseException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	private function addRootNode($name): int
	{
		if (!$this->aclValidator->isModuleAdmin($this->UID))
			throw new ModuleException('mediapool','No rights to add root node.');

		return $this->nodesRepository->addRootNode($this->UID, $name);
	}

	/**
	 * @throws ModuleException
	 * @throws Exception
	 * @throws DatabaseException
	 * @throws CoreException|PhpfastcacheSimpleCacheException
	 */
	private function addSubNode(int $parent_node_id, string $name): int
	{
		$parentNode = $this->nodesRepository->getNode($parent_node_id);
		if (empty($parentNode))
			throw new ModuleException('mediapool', 'Parent node not found');

		$rights      = $this->determineRights($parentNode);
		if (!$rights['edit'])
			throw new ModuleException('mediapool', 'No rights to add node under: ' . $parentNode['name']);

		return $this->nodesRepository->addSubNode($this->UID, $name, $parentNode);
	}

	/**
	 * @throws CoreException
	 * @throws Exception
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	private function determineRights(array $node): array
	{
		$delete     = false;
		$edit       = false;
		$rights     = $this->aclValidator->checkDirectoryPermissions($this->UID, $node);

		if(!$rights['read'])
			return ['create' => false, 'edit' => false, 'delete' => false, 'share' => ''];

		if ($rights['edit'])
		{
			if ($node['parent_id'] > 0 || $this->aclValidator->isModuleAdmin($this->UID))
			{
				$delete = true;
				$edit   = true;
			}
		}
		return ['create' => $rights['create'], 'edit' => $edit, 'delete' => $delete, 'share' => $rights['share']];
	}
}