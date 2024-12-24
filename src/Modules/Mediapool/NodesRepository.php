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

use App\Framework\Database\BaseRepositories\NestedSetTrait;
use App\Framework\Database\BaseRepositories\Sql;
use App\Framework\Database\BaseRepositories\TransactionsTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class NodesRepository extends Sql
{
	use NestedSetTrait, TransactionsTrait;

	public function __construct(Connection $connection)
	{
		parent::__construct($connection,'mediapool_nodes', 'node_id');
	}

	/**
	 * @throws Exception
	 */
	public function getNode(int $node_id): array
	{
		$select = ['media_nodes.UID,
					usr_nickname,
					company_id,
					node_id,
					is_public,
					root_id,
					parent_id,
					level,
					lft,
					rgt,
					last_updated,
					create_date,
					name,
					ROUND((rgt - lft - 1) / 2) AS children'];
		$where = ['node_id' => $node_id];
		$join  = ['user_main' => $this->table.'.UID = user_main.UID'];

		return $this->findAllByWithFields($select, $where, $join, 0, 1);
	}

}