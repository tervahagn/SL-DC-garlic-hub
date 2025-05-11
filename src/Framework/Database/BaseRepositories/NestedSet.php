<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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


namespace App\Framework\Database\BaseRepositories;

use App\Framework\Database\BaseRepositories\Traits\CrudTraits;
use App\Framework\Database\BaseRepositories\Traits\TransactionsTrait;
use App\Framework\Exceptions\DatabaseException;
use App\Framework\Exceptions\FrameworkException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Throwable;

class NestedSet extends SqlBase
{
	use CrudTraits, TransactionsTrait;

	protected NestedSetHelper $helper;
	protected LoggerInterface $logger;

	public function __construct(Connection $connection, NestedSetHelper $helper, LoggerInterface $logger, string $table, string $idField)
	{
		$this->helper = $helper;
		$this->logger = $logger;
		$this->helper->init($connection, $table);

		parent::__construct($connection, $table, $idField);
	}

	/**
	 * @throws Exception
	 */
	public function findAllRootNodes(): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('*, FLOOR((rgt-lft)/2) AS children')
			->from($this->table)
			->leftJoin($this->table,
				'user_main',
				'user_main',
				$this->table.'.UID = user_main.UID')
			->where('parent_id = 0')
			->orderBy('root_order', 'ASC');

		return $queryBuilder->executeQuery()->fetchAllAssociative();
	}

	/**
	 * @throws Exception
	 */
	public function findTreeByRootId(int $rootId): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();

		$queryBuilder->select('FLOOR((n.rgt-n.lft)/2) AS children, n.*, u.company_id')
			->from($this->table, 'n')
			->leftJoin('n', $this->table,'p','n.root_id = p.root_id')
			->leftJoin('n','user_name','u','n.UID = u.UID')
			->where('n.root_id = :root_id')
			->andWhere('n.lft BETWEEN p.lft AND p.rgt')
			->setParameter('root_id', $rootId)
			->groupBy('n.lft');

		return $queryBuilder->executeQuery()->fetchAllAssociative();
	}

	/**
	 * @throws Exception|DatabaseException
	 */
	public function findNodeOwner(int $nodeId): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('user_main.UID, node_id, name, company_id')
			->from($this->table)
			->leftJoin($this->table,
				'user_main',
				'user_main',
				$this->table.'.UID = user_main.UID')
			->where('node_id = :id')
			->orderBy('lft', 'ASC')
			->setParameter('id', $nodeId);

		$ret = $queryBuilder->executeQuery()->fetchAssociative();
		if (!$ret)
			throw new DatabaseException('Node not found');

		return $ret;
	}

	/**
	 * @throws Exception
	 */
	public function findAllChildNodesByParentNode(int $nodeId): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('*, FLOOR((rgt-lft)/2) AS children')
			->from($this->table)
			->leftJoin($this->table,
				'user_main',
				'user_main',
				$this->table.'.UID = user_main.UID')
			->where('parent_id = :id')
			->orderBy('lft', 'ASC')
			->setParameter('id', $nodeId);

		return $queryBuilder->executeQuery()->fetchAllAssociative();
	}

	/**
	 * @throws Exception
	 */
	public function findAllChildrenInTreeOfNodeId(int $nodeId): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('root_id, rgt, lft')
			->from($this->table)
			->where('node_id = :node_id')
			->setParameter('node_id', $nodeId);

		$node_data   = $queryBuilder->executeQuery()->fetchAllAssociative();

		if (empty($node_data))
			return [];

		$queryBuilder2 = $this->connection->createQueryBuilder();
		$queryBuilder2->select('node_id, category_name')
			->from($this->table)
			->where('root_id = ' . (int) $node_data[0]['root_id']. ' AND (lft BETWEEN '.$node_data[0]['lft'].' AND '.$node_data[0]['rgt']);

		return $queryBuilder2->executeQuery()->fetchAllAssociative();
	}

	/**
	 * @throws Exception
	 */
	public function findRootIdRgtAndLevelByNodeId(int $node_id):array
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('root_id, rgt, lft')
			->from($this->table)
			->where('node_id = :node_id')
			->setParameter('node_id', $node_id);

		return $this->fetchAssociative($queryBuilder);
	}

	/**
	 * returns all node ids of a given root_id
	 *
	 * @throws Exception
	 */
	public function findAllSubNodeIdsByRootIdsAndPosition(int $root_id, int $node_rgt, int $node_lft): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();

		$queryBuilder->select('node_id')
			->from($this->table)
			->where('root_id = :root_id')
			->andWhere('lft >= :node_lft')
			->andWhere('rgt <= :node_rgt')
			->setParameter('root_id', $root_id)
			->setParameter('node_lft', $node_lft)
			->setParameter('node_rgt', $node_rgt);

		return $queryBuilder->executeQuery()->fetchAllAssociative();
	}

	/**
	 * @throws DatabaseException
	 * @throws Exception
	 */
	public function addRootNode(int $UID, string $name): int
	{
		try
		{
			$nodeData = [
				'name'              => $name,
				'parent_id'         => 0,
				'root_order'        => 0,
				'visibility'	    => 0,
				'lft'               => 1,
				'rgt'               => 2,
				'UID'               => $UID,
				'level'             => 1
			];

			$this->beginTransaction();
			$newNodeId = $this->insert($nodeData);

			if ($newNodeId == 0)
				throw new DatabaseException('Insert new node failed');

			$update_fields = ['root_id' => $newNodeId, 'root_order' => $newNodeId];

			if ($this->update($newNodeId, $update_fields) === 0)
				throw new \Exception('Update root node failed');

			$this->commitTransaction();

			return $newNodeId;
		}
		catch (\Exception | Exception | DatabaseException $e)
		{
			$this->rollbackTransaction();
			throw new DatabaseException('Add root node failed because of: '.$e->getMessage());
		}
	}

	/**
	 * @throws Exception
	 * @throws DatabaseException
	 */
	public function addSubNode(int $UID, string $name, array $parentNode): int
	{
		try
		{
			$this->beginTransaction();

			$fields = array(
				'lft'       => $parentNode['rgt'],
				'rgt'       => $parentNode['rgt'] + 1,
				'parent_id' => $parentNode['node_id'],
				'root_order'=> 0,
				'visibility'=> 0,
				'root_id'   => $parentNode['root_id'],
				'level'     => $parentNode['level'] + 1,
				'name'      => $name,
				'UID'       => $UID
			);

			$this->helper->moveNodesToLeftForInsert($parentNode['root_id'], $parentNode['rgt']);
			$this->helper->moveNodesToRightForInsert($parentNode['root_id'], $parentNode['rgt']);

			$newNodeId = $this->insert($fields);
			if ($newNodeId == 0)
				throw new \Exception('Insert new sub node failed');

			$this->commitTransaction();

			return $newNodeId;
		}
		catch (\Exception $e)
		{
			$this->rollBackTransaction();
			throw new DatabaseException('Add sub node failed because of: '.$e->getMessage());
		}
	}

	/**
	 * @throws DatabaseException
	 * @throws Exception
	 */
	public function deleteSingleNode(array $node): void
	{
		try
		{
			$this->beginTransaction();

			if ($this->delete($node['node_id']) === 0 )
				throw new \Exception('not exists');

			$this->helper->moveNodesToLeftForDeletion($node['root_id'], $node['rgt']);
			$this->helper->moveNodesToRightForDeletion($node['root_id'], $node['rgt']);

			$this->commitTransaction();
		}
		catch (\Exception $e)
		{
			$this->rollbackTransaction();
			throw new DatabaseException('delete single node failed because of: '.$e->getMessage());
		}
	}

	/**
	 * @throws DatabaseException
	 * @throws Exception
	 */
	public function deleteTree(array $node): void
	{
		try
		{
			$this->beginTransaction();

			if ($this->helper->deleteFullTree($node['root_id'], $node['rgt'], $node['lft']) === 0)
				throw new FrameworkException('not exists');

			// remove other nodes to create some space
			$move = floor(($node['rgt'] - $node['lft']) / 2);
			$move = 2 * (1 + $move);

			$this->helper->moveNodesToLeftForDeletion($node['root_id'], $node['rgt'], $move);
			$this->helper->moveNodesToRightForDeletion($node['root_id'], $node['rgt'], $move);

			$this->commitTransaction();
		}
		catch (Throwable $e)
		{
			$this->rollbackTransaction();
			throw new DatabaseException('Delete tree failed because of: '.$e->getMessage());
		}
	}

	/**
	 * @throws Exception
	 * @throws FrameworkException
	 * @throws DatabaseException
	 */
	public function moveNode(array $movedNode, array $targetNode, string $region): void
	{
		try
		{
			$this->connection->beginTransaction();

			$diff_level = $this->helper->calculateDiffLevelByRegion($region, $movedNode['level'], $targetNode['level']);
			$newLgtPos  = $this->helper->determineLgtPositionByRegion($region, $targetNode);
			$width      = $movedNode['rgt'] - $movedNode['lft'] + 1;

			// https://rogerkeays.com/how-to-move-a-node-in-nested-sets-with-sql
			// enhanced for including multiple trees with different root_id's in datatable

			// create some space in target
			$this->helper->moveNodesToRightForInsert($targetNode['root_id'], $newLgtPos, $width);
			$this->helper->moveNodesToLeftForInsert($targetNode['root_id'], $newLgtPos, $width);

			$i = $this->helper->moveSubTree($movedNode, $targetNode, $newLgtPos, $width, $diff_level);
			if ($i === 0)
				throw new FrameworkException($movedNode['name']. ' cannot be moved via '.$region.' of '. $targetNode['name']);

			$this->update($movedNode['node_id'], ['parent_id' => $this->helper->determineParentIdByRegion($region,
				$targetNode)]);

			// close source space if not a root node
			if ($movedNode['parent_id'] !== 0)
			{
				$this->helper->moveNodesToRightForDeletion($movedNode['root_id'], $movedNode['rgt'], $width);
				$this->helper->moveNodesToLeftForDeletion($movedNode['root_id'], $movedNode['rgt'], $width);
			}


			$this->connection->commit();
		}
		catch (Throwable $e)
		{
			$this->connection->rollBack();
			throw new DatabaseException('Moving nodes failed: '.$e->getMessage());
		}
	}

}