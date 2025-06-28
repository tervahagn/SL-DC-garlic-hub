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


namespace App\Modules\Mediapool\Repositories;

use App\Framework\Database\BaseRepositories\SqlBase;
use App\Framework\Database\BaseRepositories\Traits\CrudTraits;
use App\Framework\Database\BaseRepositories\Traits\FindOperationsTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class FilesRepository extends SqlBase
{
	use CrudTraits, FindOperationsTrait;

	public function __construct(Connection $connection)
	{
		parent::__construct($connection,'mediapool_files', 'media_id');
	}

	/**
	 * @return array<string,mixed>|array<empty,empty>
	 * @throws Exception
	 */
	public function findAllWithOwnerById(string $mediaId): array
	{
		$select     = ['user_main.username', 'company_id', 'media_id', $this->table.'.UID', 'node_id', 'upload_time', 'checksum', 'mimetype', 'metadata', 'tags', 'filename', 'extension', 'thumb_extension', 'media_description', 'config_data'];
		$join       = ['user_main' => 'user_main.UID=' . $this->table . '.UID'];
		$where      = [
			'media_id' => $this->generateWhereClause($mediaId),
			'deleted' => $this->generateWhereClause(0)
		];

		return $this->getFirstDataSet($this->findAllByWithFields($select, $where, $join));
	}

	/**
	 * @return array<string,mixed>|array<empty,empty>
	 * @throws Exception
	 */
	public function findAllWithOwnerByCheckSum(string $checksum): array
	{
		$select     = ['user_main.username', 'company_id', 'media_id', $this->table.'.UID', 'node_id', 'upload_time', 'checksum', 'mimetype', 'metadata', 'tags', 'filename', 'extension', 'thumb_extension', 'media_description', 'config_data'];
		$join       = ['user_main' => 'user_main.UID=' . $this->table . '.UID'];
		$where      = [
			'checksum' => $this->generateWhereClause($checksum)
		];

		return $this->getFirstDataSet($this->findAllByWithFields($select, $where, $join));
	}

	/**
	 * @return list<array<string,mixed>>
	 * @throws Exception
	 */
	public function findAllByNodeId(int $nodeId): array
	{
		$select     = ['user_main.username', 'company_id', 'media_id', 'node_id', $this->table.'.UID', 'upload_time', 'checksum', 'mimetype', 'metadata', 'tags', 'filename', 'extension', 'thumb_extension', 'media_description'];
		$join       = ['user_main' => 'user_main.UID=' . $this->table . '.UID'];
		$where      = [
			'node_id' => $this->generateWhereClause($nodeId),
			'deleted' => $this->generateWhereClause(0)
		];

		$order_by   = [['sort' => 'upload_time', 'order' => 'DESC']];

		return $this->findAllByWithFields($select, $where, $join, [], '', $order_by);
	}

}