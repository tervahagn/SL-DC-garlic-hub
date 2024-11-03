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

namespace App\Modules\Mediapool\Entities;

use App\Framework\BaseRepositories\Sql;
use App\Framework\Database\DBHandler;
use App\Framework\Database\Helpers\DataPreparer;
use App\Framework\Database\QueryBuilder;

class MediaNodes extends Sql
{

	public function __construct(DBHandler $dbh, QueryBuilder $queryBuilder, DataPreparer $dataPreparer, string $table, string $id_field)
	{
		parent::__construct($dbh, $queryBuilder, $dataPreparer, 'mediapool_nodes', 'node_id');
	}

	/**
	 * @param int $node_id
	 *
	 * @return array
	 */
	public function getNode(int $node_id): array
	{
		$select = 'media_nodes.UID,
					username,
					company_id,
					node_id,
					domain_ids,
					is_public,
					root_id,
					parent_id,
					level,
					lft,
					rgt,
					last_updated,
					create_date,
					name,
					storage_type,
					credentials
					ROUND((rgt - lft - 1) / 2) AS children';

		$join   = 'LEFT JOIN user_main USING(UID)';
		$where  = 'node_id=' . $node_id;
		return $this->findAllByWithFields($select, $where, $join, '1');
	}

	/**
	 * @return array
	 */
	public function findAllRootNodes(): array
	{
		$select     = '*, floor((rgt-lft)/2) AS children';
		$join       = 'LEFT JOIN user_main USING(UID)';
		$where      = 'parent_id=0';
		$order_by   = 'root_order ASC';
		return $this->findAllByWithFields($select, $where, $join, '', '', $order_by);
	}

	/**
	 * @param int $root_id
	 *
	 * @return  array
	 */
	public function findTreeByRootId(int $root_id): array
	{
		$join   = 'RIGHT JOIN ' . $this->getTable() . ' AS p USING(root_id) ' .
			'LEFT JOIN user_main as u ON n.UID = u.UID';

		$sql = $this->QueryBuilder->buildSelectQuery(
			'floor((n.rgt-n.lft)/2) AS children, n.*, u.company_id',
			$this->getTable() . ' AS n',
			'n.root_id = '. $root_id . ' AND n.lft BETWEEN p.lft AND p.rgt',
			$join,
			'',
			'n.lft',
		);

		return $this->getDbh()->select($sql);
	}

	/**
	 * get all direct children
	 *
	 * @param int $node_id
	 *
	 * @return  array
	 */
	public function findAllChildNodesByParentNode(int $node_id): array
	{
		$select     = '*, floor((rgt-lft)/2) AS children';
		$join       = 'LEFT JOIN user_main USING(UID)';
		$where      = 'parent_id = ' . $node_id;
		return $this->findAllByWithFields($select, $where, $join);
	}

	/**
	 * @param int $node_id
	 *
	 * @return  array
	 */
	public function findAllChildrenInTreeOfNodeId(int $node_id): array
	{
		$node_data  = $this->findAllByWithFields(
			'root_id, rgt, lft',
			'node_id = ' . $node_id
		);

		if (empty($node_data))
			return array();

		return $this->findAllByWithFields(
			'node_id, name',
			'root_id = ' . (int) $node_data[0]['root_id']. ' AND (lft BETWEEN '.$node_data[0]['lft'].' AND '.$node_data[0]['rgt']. ')'
		);
	}

	/**
	 * @param   int $node_id
	 * @return  array
	 */
	public function findRootIdRgtAndLevelByNodeId(int $node_id): array
	{
		$result = $this->findAllByWithFields(
			'root_id, rgt, level',
			'node_id = ' . $node_id
		);

		return $this->getFirstDataSet($result);
	}

	/**
	 * moves existing nodes out of the way for inserting new nodes
	 *
	 * @param int $root_id
	 * @param int $position
	 *
	 * @return int
	 */
	public function moveNodesToLeftForInsert(int $root_id, int $position): int
	{
		$sql = $this->QueryBuilder->buildUpdateQuery(
			$this->getTable(),
			array('lft' => 'lft+2'),
			'root_id = ' . $root_id . ' AND lft > ' . $position
		);

		return $this->getDbh()->update($sql);
	}

	/**
	 * moves existing nodes out of the way for inserting new nodes
	 *
	 * @param int   $root_id
	 * @param int   $position
	 * @return int
	 */
	public function moveNodesToRightForInsert(int $root_id, int $position): int
	{
		$sql = $this->QueryBuilder->buildUpdateQuery(
			$this->getTable(),
			array('rgt' => 'rgt+2'),
			'root_id = ' . $root_id . ' AND rgt > ' . $position
		);

		return $this->getDbh()->update($sql);
	}

	/**
	 * @param   int     $root_id
	 * @param   int     $position
	 * @return int
	 */
	public function moveNodesToRightForDeletion(int $root_id, int $position): int
	{
		return $this->moveNodesToRightForDeletionWithSteps( $root_id,  $position, 2);
	}

	/**
	 * @param   int     $root_id
	 * @param   int     $position
	 * @return int
	 */
	public function moveNodesToLeftForDeletion(int $root_id, int $position): int
	{
		return $this->moveNodesToLeftForDeletionWithSteps($root_id, $position, 2);
	}

	/**
	 * @param   int     $root_id
	 * @param   int     $position
	 * @param   int     $steps
	 * @return  int
	 */
	public function moveNodesToLeftForDeletionWithSteps(int $root_id, int $position, int $steps): int
	{
		$sql = $this->QueryBuilder->buildUpdateQuery(
			$this->getTable(),
			array('lft' => 'lft - ' . $steps),
			'root_id = ' . $root_id . ' AND lft > '. $position
		);

		return $this->getDbh()->update($sql);
	}

	/**
	 * @param   int     $root_id
	 * @param   int     $position
	 * @param   int     $steps
	 * @return  int
	 */
	public function moveNodesToRightForDeletionWithSteps(int $root_id, int $position, int $steps): int
	{
		$sql = $this->QueryBuilder->buildUpdateQuery(
			$this->getTable(),
			array('rgt' => 'rgt - ' . $steps),
			'root_id = ' . $root_id . ' AND rgt > ' . $position
		);

		return $this->getDbh()->update($sql);
	}

	/**
	 * returns all node ids of a given root_id
	 *
	 * @param   int     $root_id
	 * @param   int     $node_rgt
	 * @param   int     $node_lft
	 * @return array
	 */
	public function findAllSubNodeIdsByRootIdsAndPosition(int $root_id, int $node_rgt, int $node_lft): array
	{
		$select = 'node_id';
		$where  = 'root_id = ' . $root_id . ' AND lft >= ' . $node_lft . ' AND rgt <= ' . $node_rgt;
		return $this->findAllByWithFields($select, $where);
	}

	/**
	 * @param int $root_id
	 * @param int $pos_rgt
	 * @param int $pos_lft
	 *
	 * @return  bool
	 */
	public function deleteFullTree(int $root_id, int $pos_rgt, int $pos_lft): bool
	{
		$where = 'root_id = ' . $root_id . ' AND lft between ' . $pos_lft . ' AND ' . $pos_rgt;
		return $this->deleteBy($where);
	}

}