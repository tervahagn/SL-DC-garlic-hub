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
	public function findTreeByRootId(int $root_id): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('FLOOR((n.rgt-n.lft)/2) AS children, n.*, u.company_id')
			->from($this->table, 'n')
			->leftJoin('n', $this->table,'p','n.root_id = p.root_id')
			->leftJoin('n','user_name','u','n.UID = u.UID')
			->where('n.root_id = :root_id')
			->andWhere('n.lft BETWEEN p.lft AND p.rgt')
			->setParameter('root_id', $root_id)
			->groupBy('n.lft');

		return $queryBuilder->executeQuery()->fetchAllAssociative();
	}

	/**
	 * @throws Exception
	 */
	public function findAllChildNodesByParentNode(int $node_id): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('*, FLOOR((rgt-lft)/2) AS children')
			->from($this->table)
			->leftJoin($this->table,
				'user_main',
				'user_main',
				$this->table.'.UID = user_main.UID')
			->where('parent_id = :id')
			->orderBy('lft ASC', )
			->setParameter('id', $node_id);

		return $queryBuilder->executeQuery()->fetchAllAssociative();
	}

	/**
	 * @throws Exception
	 */
	public function findAllChildrenInTreeOfNodeId(int $node_id): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('root_id, rgt, lft')
			->from($this->table)
			->where('node_id = :node_id')
			->setParameter('node_id', $node_id);


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
	 * @throws Exception
	 */
	public function deleteFullTree($root_id, $pos_rgt, $pos_lft): int
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


	/**
	 * @throws Exception
	 * @throws FrameworkException
	 */
	public function moveNode(int $sourceId, int $targetId, string $region): void
	{
		try
		{
			$this->connection->beginTransaction();
			$movedNode = $this->getNode($sourceId);
			$targetNode = $this->getNode($targetId);

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
	public function moveSubTree(array $movedNode, array $targetNode, int $newLgtPos, int $width, int $diffLevel):	int
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
	public function moveNodesToRightForInsert(int $root_id, int $position, int $width = 2): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->update($this->table)
					 ->set('lft', 'lft + :steps')
					 ->where('root_id = :root_id')
					 ->andWhere('lft >= :position')
					 ->setParameter('steps', $width)
					 ->setParameter('root_id', $root_id)
					 ->setParameter('position', $position);

		return (int) $queryBuilder->executeStatement();
	}

	public function moveNodesToLeftForInsert(int $root_id, int $position, int $width = 2): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->update($this->table)
			->set('rgt', 'rgt + :steps')
			->where('root_id = :root_id')
			->andWhere('rgt >= :position')
			->setParameter('steps', $width)
			->setParameter('root_id', $root_id)
			->setParameter('position', $position);

		return (int) $queryBuilder->executeStatement();
	}

	/**
	 * @throws Exception
	 */
	public function moveNodesToLeftForDeletion(int $root_id, int $position, int $width = 2): int
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
	public function moveNodesToRightForDeletion(int $root_id, int $position, int $width = 2): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->update($this->table)
					 ->set('rgt', 'rgt - '.$width)
					 ->where('root_id = :root_id')
					 ->andWhere('rgt > :position')
					 ->setParameter('root_id', $root_id)
					 ->setParameter('position', $position);

		return (int) $queryBuilder->executeStatement();
	}

	protected function determineLgtPositionByRegion(string $region, array $node): int
	{
		switch ($region)
		{
			case self::REGION_BEFORE:
				// if target position is before a node, newpos = left position of this node
				$newLgtPos = $node['lft'];
				break;
			case self::REGION_APPENDCHILD:
				// if target position is into a node (as a child), newpos = left position of this node + 1
				$newLgtPos = $node['lft'] + 1;
				break;
			case self::REGION_AFTER:
				// if target position is after a node, newpos = right position of this node + 1
				$newLgtPos = $node['rgt'] + 1;
				break;
			default:
				throw new FrameworkException('Unknown region: '.$region );
		}
		return $newLgtPos;
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

}