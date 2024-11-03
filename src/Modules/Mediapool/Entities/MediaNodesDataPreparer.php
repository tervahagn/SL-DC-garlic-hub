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

use App\Framework\Database\Helpers\DataPreparer;

class MediaNodesDataPreparer extends DataPreparer
{

	public function prepareForDB(array $fields): array
	{
		$fields_to_quote = [
			'node_id'      => self::FIELD_TYPE_INTEGER,
			'root_id'      => self::FIELD_TYPE_INTEGER,
			'parent_id'    => self::FIELD_TYPE_INTEGER,
			'level'        => self::FIELD_TYPE_INTEGER,
			'root_order'   => self::FIELD_TYPE_INTEGER,
			'lft'          => self::FIELD_TYPE_INTEGER,
			'rgt'          => self::FIELD_TYPE_INTEGER,
			'UID'          => self::FIELD_TYPE_INTEGER,
			'domain_ids'   => self::FIELD_TYPE_INTEGER,
			'is_public'    => self::FIELD_TYPE_INTEGER,
			'last_updated' => self::FIELD_TYPE_DATETIME,
			'create_date'  => self::FIELD_TYPE_DATETIME,
			'name'         => self::FIELD_TYPE_STRING,
			'storage_type' => self::FIELD_TYPE_STRING,
			'credentials'  => self::FIELD_TYPE_STRING,
		];

		foreach ($fields_to_quote as $field => $type)
		{
			$fields = $this->quoteField($field, $fields, $type);
		}

		return $fields;
	}
}