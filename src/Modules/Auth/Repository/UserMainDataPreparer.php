<?php

namespace App\Modules\Auth\Repository;

use App\Framework\Database\Helpers\DataPreparer;

/**
 * Prepares user data fields for database insertion.
 */
class UserMainDataPreparer extends DataPreparer
{
	/**
	 * Quotes and formats fields based on their type.
	 *
	 * @param array $fields Data fields to prepare
	 * @return array Prepared fields for database
	 */
	public function prepareForDB(array $fields): array
	{
		$fields_to_quote = [
			'UID'       => self::FIELD_TYPE_INTEGER,
			'company_id'=> self::FIELD_TYPE_INTEGER,
			'lastaccess'=> self::FIELD_TYPE_DATETIME,
			'logintime' => self::FIELD_TYPE_DATETIME,
			'since'     => self::FIELD_TYPE_DATETIME,
			'2fa'       => self::FIELD_TYPE_STRING,
			'status'    => self::FIELD_TYPE_INTEGER,
			'logged'    => self::FIELD_TYPE_INTEGER,
			'lastIP'    => self::FIELD_TYPE_IP,
			'birthday'  => self::FIELD_TYPE_DATETIME,
			'locale'    => self::FIELD_TYPE_STRING,
			'SID'       => self::FIELD_TYPE_STRING,
			'username'  => self::FIELD_TYPE_STRING,
			'password'  => self::FIELD_TYPE_STRING,
			'gender'    => self::FIELD_TYPE_STRING,
			'email'     => self::FIELD_TYPE_STRING,
		];

		foreach ($fields_to_quote as $field => $type) {
			$fields = $this->quoteField($field, $fields, $type);
		}

		return $fields;
	}
}
