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

class MediaDataPreparer extends DataPreparer
{

	public function prepareForDB(array $fields): array
	{
		$fields_to_quote = [
			'media_id'          => self::FIELD_TYPE_INTEGER,
			'node_id'           => self::FIELD_TYPE_INTEGER,
			'deleted'           => self::FIELD_TYPE_INTEGER,
			'preview'           => self::FIELD_TYPE_INTEGER,
			'last_UID'          => self::FIELD_TYPE_INTEGER,
			'last_update'       => self::FIELD_TYPE_DATETIME,
			'UID'               => self::FIELD_TYPE_INTEGER,
			'upload_time'       => self::FIELD_TYPE_DATETIME,
			'filetype'          => self::FIELD_TYPE_STRING,
			'filename'          => self::FIELD_TYPE_STRING,
			'filesize'          => self::FIELD_TYPE_INTEGER,
			'duration'          => self::FIELD_TYPE_INTEGER,
			'mediatype'         => self::FIELD_TYPE_STRING,
			'tags'              => self::FIELD_TYPE_STRING,
			'media_description' => self::FIELD_TYPE_STRING,
		];

		foreach ($fields_to_quote as $field => $type)
		{
			$fields = $this->quoteField($field, $fields, $type);
		}

		return $fields;
	}
}