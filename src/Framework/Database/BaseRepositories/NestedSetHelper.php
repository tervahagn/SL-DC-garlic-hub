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

use App\Framework\Exceptions\DatabaseException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * NestedSetBase class provides functionalities for managing hierarchical data
 * following the Nested Set Model in a relational database.
 *
 * This class includes several utility methods to manipulate tree structures,
 * such as moving nodes or entire subtrees, determining positional parameters
 * based on regions, and deleting parts or the entirety of a tree.
 *
 * Key constants define positional regions for node placement relative to
 * references within the hierarchical data structure.
 */
class NestedSetHelper
{
	const string REGION_BEFORE = 'before';
	const string REGION_AFTER = 'after';
	const string REGION_APPENDCHILD = 'appendChild';

	private string $table;
	private Connection $connection;

	public function init(Connection $connection, string $table): void
	{
		$this->connection   = $connection;
		$this->table        = $table;
	}

	/**
	 * @throws Exception
	 */
	public function moveSubTree(array $movedNode, array $targetNode, int $newLgtPos, int $width, int $diffLevel):
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
	public function moveNodesToRightForInsert(int $rootId, int $position, int $width = 2): int
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
	public function moveNodesToLeftForInsert(int $rootId, int $position, int $width = 2): int
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
	public function moveNodesToLeftForDeletion(int $rootId, int $position, int $width = 2): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->update($this->table)
			->set('lft', 'lft - '.$width)
			->where('root_id = :root_id')
			->andWhere('lft > :position')
			->setParameter('root_id', $rootId)
			->setParameter('position', $position);

		return (int) $queryBuilder->executeStatement();
	}

	/**
	 * @throws Exception
	 */
	public function moveNodesToRightForDeletion(int $rootId, int $position, int $width = 2): int
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
	public function determineLgtPositionByRegion(string $region, array $node): int
	{
		return match ($region)
		{
			self::REGION_BEFORE => $node['lft'],
			self::REGION_APPENDCHILD => $node['rgt'],
			self::REGION_AFTER => $node['rgt'] + 1,
			default => throw new DatabaseException('Unknown region: ' . $region),
		};
	}

	public function calculateDiffLevelByRegion(string $region, int $movedLevel, int $targetLevel): int
	{
		$diffLevel = $targetLevel - $movedLevel;

		if ($region === self::REGION_APPENDCHILD)
		{
			$diffLevel++;
		}

		return $diffLevel;
	}

	public function determineParentIdByRegion(string $region, array $node): int
	{
		if ($region !== self::REGION_APPENDCHILD)
			return $node['parent_id'];

		return $node['node_id'];
	}

	/**
	 * @throws Exception
	 */
	public function deleteFullTree($rootId, $posRgt, $posLft): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->delete($this->table)
			->where('root_id = :root_id')
			->andWhere('lft between :pos_lft AND :pos_rgt')
			->setParameter('root_id', $rootId)
			->setParameter('pos_lft', $posLft)
			->setParameter('pos_rgt', $posRgt);

		return (int) $queryBuilder->executeStatement();
	}


}