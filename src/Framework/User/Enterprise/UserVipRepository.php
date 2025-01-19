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

namespace App\Framework\User\Enterprise;

use App\Framework\Database\BaseRepositories\Sql;
use Doctrine\DBAL\Connection;

class UserVipRepository extends Sql
{
	public function __construct(Connection $connection)
	{
		parent::__construct($connection,'user_security', 'UID');
	}

	public function findOneAclByUIDModuleAndDataNum(int $UID, string $module, int $data_num): int // maybe we need local acls later
	{
	/*	$field = 'acl';
		$where = 'UID = ' . (int) $UID . ' AND module = ' . $this->quoteString($module) . ' AND data_num = ' . $data_num;
		return $this->findOneValueBy($field, $where);
	*/
	}

	/**
	 * "Active" means all entries with an ACL value > 0
	 */
	public function findAllActiveDataNumsByUIDModule(int $UID, string $module): array
	{
		/*
		$field = 'data_num';
		$where = 'UID = ' . (int) $UID . ' AND module = ' . $this->quoteString($module);
		return $this->findAllByWithFields($field, $where);
		*/
	}
}