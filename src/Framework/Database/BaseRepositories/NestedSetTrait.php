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

use Doctrine\DBAL\Exception;

trait NestedSetTrait
{
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
	 * moves existing nodes out of the way for inserting new nodes
	 *
	 * @throws Exception
	 */
	public function moveNodesToLeftForInsert(int $root_id, int $position): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->update($this->table)
					 ->set('lft', 'lft + 2')
					 ->where('root_id = :root_id')
					 ->andWhere('lft > :position')
					 ->setParameter('root_id', $root_id)
					 ->setParameter('position', $position);

		return (int) $queryBuilder->executeStatement();
	}

	/**
	 * moves existing nodes out of the way for inserting new nodes
	 *
	 * @throws Exception
	 */
	public function moveNodesToRightForInsert(int $root_id, int $position): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->update($this->table)
					 ->set('rgt', 'rgt + 2')
					 ->where('root_id = :root_id')
					 ->andWhere('rgt >= :position')
					 ->setParameter('root_id', $root_id)
					 ->setParameter('position', $position);

		return (int) $queryBuilder->executeStatement();
	}

	/**
	 * @throws Exception
	 */
	public function moveNodesToRightForDeletion(int $root_id, int $position): int
	{
		return $this->moveNodesToRightForDeletionWithSteps($root_id, $position, 2);
	}

	/**
	 * @throws Exception
	 */
	public function moveNodesToLeftForDeletion(int $root_id, int $position): int
	{
		return $this->moveNodesToLeftForDeletionWithSteps($root_id, $position, 2);
	}

	/**
	 * @throws Exception
	 */
	public function moveNodesToLeftForDeletionWithSteps(int $root_id, int $position, int $steps): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->update($this->table)
					 ->set('lft', 'lft - '.$steps)
					 ->where('root_id = :root_id')
					 ->andWhere('lft > :position')
					 ->setParameter('root_id', $root_id)
					 ->setParameter('position', $position);

		return (int) $queryBuilder->executeStatement();
	}

	/**
	 * @throws Exception
	 */
	public function moveNodesToRightForDeletionWithSteps(int $root_id, int $position, int $steps): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->update($this->table)
					 ->set('rgt', 'rgt - '.$steps)
					 ->where('root_id = :root_id')
					 ->andWhere('rgt > :position')
					 ->setParameter('root_id', $root_id)
					 ->setParameter('position', $position);

		return (int) $queryBuilder->executeStatement();
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
}