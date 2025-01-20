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

namespace App\Framework\Database\BaseRepositories;

use App\Framework\Exceptions\DatabaseException;
use App\Framework\Exceptions\FrameworkException;
use Doctrine\DBAL\Exception;

trait NestedSetTrait
{
	const string REGION_BEFORE = 'before';
	const string REGION_AFTER = 'after';
	const string REGION_APPENDCHILD = 'appendChild';

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
					 ->orderBy('root_order ASC');

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
			->orderBy('lft ASC')
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
				'is_public'		    => 0,
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
				'is_public' => 0,
				'root_id'   => $parentNode['root_id'],
				'level'     => $parentNode['level'] + 1,
				'name'      => $name,
				'UID'       => $UID
			);

			$this->moveNodesToLeftForInsert($parentNode['root_id'], $parentNode['rgt']);
			$this->moveNodesToRightForInsert($parentNode['root_id'], $parentNode['rgt']);

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
	protected function deleteSingleNode(array $node): void
	{
		try
		{
			$this->beginTransaction();

			$this->delete($node['node_id']);
			$this->moveNodesToLeftForDeletion($node['root_id'], $node['rgt']);
			$this->moveNodesToRightForDeletion($node['root_id'], $node['rgt']);

			$this->commitTransaction();
		}
		catch (Exception $e)
		{
			$this->rollbackTransaction();
			throw new DatabaseException('delete single node failed because of: '.$e->getMessage());
		}
	}

	/**
	 * @throws DatabaseException
	 * @throws Exception
	 */
	protected function deleteTree(array $node): void
	{
		try
		{
			$this->beginTransaction();

			$this->deleteFullTree($node['root_id'], $node['rgt'], $node['lft']);

			// remove other nodes to create some space
			$move = floor(($node['rgt'] - $node['lft']) / 2);
			$move = 2 * (1 + $move);

			$this->moveNodesToLeftForDeletion($node['root_id'], $node['rgt'], $move);
			$this->moveNodesToRightForDeletion($node['root_id'], $node['rgt'], $move);

			$this->commitTransaction();
		}
		catch (Exception $e)
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
	public function moveNode(int $sourceId, int $targetId, string $region): void
	{
		try
		{
			$this->connection->beginTransaction();

			$movedNode = $this->getNode($sourceId);
			$targetNode = $this->getNode($targetId);

			// prevent creating new root dir

			if ($region === self::REGION_APPENDCHILD && $targetId === 0)
				throw new FrameworkException('Create root node with move is not allowed');

			if (($region === self::REGION_BEFORE || $region === self::REGION_AFTER) && $targetNode['parent_id'] === 0)
				throw new FrameworkException('Create root node with move is not allowed');

			$diff_level = $this->calculateDiffLevelByRegion($region, $movedNode['level'], $targetNode['level']);
			$newLgtPos  = $this->determineLgtPositionByRegion($region, $targetNode);
			$width      = $movedNode['rgt'] - $movedNode['lft'] + 1;

			// https://rogerkeays.com/how-to-move-a-node-in-nested-sets-with-sql
			// enhanced for including multiple trees with different root_id's in datatable

			// create some space in target
			$this->moveNodesToRightForInsert($targetNode['root_id'], $newLgtPos, $width);
			$this->moveNodesToLeftForInsert($targetNode['root_id'], $newLgtPos, $width);

			$i = $this->moveSubTree($movedNode, $targetNode, $newLgtPos, $width, $diff_level);
			if ($i === 0)
				throw new FrameworkException($movedNode['name']. ' cannot be moved via '.$region.' of '. $targetNode['name']);

			$this->update($sourceId, ['parent_id' => $this->determineParentIdByRegion($region, $targetNode)]);

			// close source space if not a root node
			if ($movedNode['parent_id'] !== 0)
			{
				$this->moveNodesToRightForDeletion($movedNode['root_id'], $movedNode['rgt'], $width);
				$this->moveNodesToLeftForDeletion($movedNode['root_id'], $movedNode['rgt'], $width);
			}


			$this->connection->commit();
		}
		catch (Exception $e)
		{
			$this->connection->rollBack();
			throw new DatabaseException('Moving nodes failed: '.$e->getMessage());
		}
	}

	/**
	 * @throws Exception
	 */
	protected function moveSubTree(array $movedNode, array $targetNode, int $newLgtPos, int $width, int $diffLevel):
	int
	{
		$distance = $newLgtPos - $movedNode['lft'];
		$tmpPos   = $movedNode['lft'];
		if ($distance < 0 && $movedNode['root_id'] === $targetNode['root_id'])
		{
			$distance -= $width;
			$tmpPos   += $width;
		}

		$queryBuilder = $this->connection->createQueryBuilder();

		$queryBuilder->update($this->table)
			->set('lft', 'lft + :distance')
			->set('rgt', 'rgt + :distance')
			->set('level', 'level + :diff_level')
			->set('root_id', ':target_root_id')
			->where('root_id = :moved_root_id')
			->andWhere('lft >= :tmpPos')
			->andWhere('rgt < :tmpPos + :width')
			->setParameter('distance', $distance)
			->setParameter('diff_level', $diffLevel)
			->setParameter('target_root_id', $targetNode['root_id'])
			->setParameter('moved_root_id', $movedNode['root_id'])
			->setParameter('tmpPos', $tmpPos)
			->setParameter('width', $width)
		;

		return (int) $queryBuilder->executeStatement();
	}


	/**
	 * moves existing nodes out of the way for inserting new nodes
	 *
	 * @throws Exception
	 */
	protected function moveNodesToRightForInsert(int $rootId, int $position, int $width = 2): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->update($this->table)
					 ->set('lft', 'lft + :steps')
					 ->where('root_id = :root_id')
					 ->andWhere('lft >= :position')
					 ->setParameter('steps', $width)
					 ->setParameter('root_id', $rootId)
					 ->setParameter('position', $position);

		return (int) $queryBuilder->executeStatement();
	}

	/**
	 * @throws Exception
	 */
	protected function moveNodesToLeftForInsert(int $rootId, int $position, int $width = 2): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->update($this->table)
			->set('rgt', 'rgt + :steps')
			->where('root_id = :root_id')
			->andWhere('rgt >= :position')
			->setParameter('steps', $width)
			->setParameter('root_id', $rootId)
			->setParameter('position', $position);

		return (int) $queryBuilder->executeStatement();
	}

	/**
	 * @throws Exception
	 */
	protected function moveNodesToLeftForDeletion(int $root_id, int $position, int $width = 2): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->update($this->table)
					 ->set('lft', 'lft - '.$width)
					 ->where('root_id = :root_id')
					 ->andWhere('lft > :position')
					 ->setParameter('root_id', $root_id)
					 ->setParameter('position', $position);

		return (int) $queryBuilder->executeStatement();
	}

	/**
	 * @throws Exception
	 */
	protected function moveNodesToRightForDeletion(int $rootId, int $position, int $width = 2): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->update($this->table)
					 ->set('rgt', 'rgt - '.$width)
					 ->where('root_id = :root_id')
					 ->andWhere('rgt > :position')
					 ->setParameter('root_id', $rootId)
					 ->setParameter('position', $position);

		return (int) $queryBuilder->executeStatement();
	}

	/**
	 * @throws DatabaseException
	 */
	protected function determineLgtPositionByRegion(string $region, array $node): int
	{
		return match ($region)
		{
			self::REGION_BEFORE => $node['lft'],
			self::REGION_APPENDCHILD => $node['lft'] + 1,
			self::REGION_AFTER => $node['rgt'] + 1,
			default => throw new DatabaseException('Unknown region: ' . $region),
		};
	}

	protected function calculateDiffLevelByRegion(string $region, int $movedLevel, int $targetLevel): int
	{
		$diffLevel = $targetLevel - $movedLevel;

		if ($region === self::REGION_APPENDCHILD)
		{
			$diffLevel++;
		}

		return $diffLevel;
	}

	protected function determineParentIdByRegion(string $region, array $node): int
	{
		if ($region !== self::REGION_APPENDCHILD)
			return $node['parent_id'];

		return $node['node_id'];
	}

	/**
	 * @throws Exception
	 */
	protected function deleteFullTree($root_id, $pos_rgt, $pos_lft): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->delete($this->getTable())
			->where('root_id = :root_id')
			->andWhere('lft between :pos_lft AND :pos_rgt')
			->setParameter('root_id', $root_id)
			->setParameter('pos_lft', $pos_lft)
			->setParameter('pos_rgt', $pos_rgt);

		return (int) $queryBuilder->executeStatement();
	}
}