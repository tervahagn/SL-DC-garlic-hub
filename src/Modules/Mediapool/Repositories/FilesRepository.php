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

use App\Framework\Database\BaseRepositories\Sql;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class FilesRepository extends Sql
{
	public function __construct(Connection $connection)
	{
		parent::__construct($connection,'mediapool_files', 'media_id');
	}

	public function findAllWithOwnerById(string $media_id): array
	{
		$select     = ['user_main.username', 'company_id', 'media_id', $this->table.'.UID', 'upload_time', 'checksum', 'mimetype', 'metadata', 'tags', 'filename', 'extension', 'thumb_extension', 'media_description'];
		$join       = ['user_main' => 'user_main.UID=' . $this->table . '.UID'];
		$where      = ['media_id' => $media_id, 'deleted' => 0];

		return $this->getFirstDataSet($this->findAllByWithFields($select, $where, $join));
	}


	/**
	 * @throws Exception
	 */
	public function findAllByNodeId(int $node_id): array
	{
		$select     = ['user_main.username', 'company_id', 'media_id', 'node_id', $this->table.'.UID', 'upload_time', 'checksum', 'mimetype', 'metadata', 'tags', 'filename', 'extension', 'thumb_extension', 'media_description'];
		$join       = ['user_main' => 'user_main.UID=' . $this->table . '.UID'];
		$where      = ['node_id' => $node_id, 'deleted' => 0];
		$order_by   = 'upload_time DESC';

		return $this->findAllByWithFields($select, $where, $join, null, null, '', $order_by);
	}
}