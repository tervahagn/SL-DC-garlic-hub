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

namespace App\Modules\Player\Helper\Datatable;

use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Framework\Utils\FormParameters\ScalarType;

class Parameters extends BaseFilterParameters
{
	const string PARAMETER_ACTIVITY = 'activity';
	const string PARAMETER_PLAYER_NAME = 'player_name';
	const string PARAMETER_PLAYER_UUID = 'uuid';
	const string PARAMETER_PLAYER_STATUS = 'status';
	const string PARAMETER_PLAYER_ID = 'player_id';
	const string PARAMETER_PLAYLIST_ID = 'playlist_id';

	protected array $moduleParameters = array(
		self::PARAMETER_ACTIVITY => array('scalar_type' => ScalarType::STRING, 'default_value' => '', 'parsed' => false),
		self::PARAMETER_PLAYER_NAME => array('scalar_type' => ScalarType::STRING, 'default_value' => '', 'parsed' => false),
		self::PARAMETER_PLAYER_STATUS => array('scalar_type' => ScalarType::INT, 'default_value' => 0, 'parsed' => false),
		self::PARAMETER_PLAYER_UUID => array('scalar_type' => ScalarType::STRING, 'default_value' => '', 'parsed' => false),
		self::PARAMETER_PLAYLIST_ID => array('scalar_type' => ScalarType::INT, 'default_value' => 0, 'parsed' => false),
	);

	/**
	 * @throws ModuleException
	 */
	public function __construct(Sanitizer $sanitizer, Session $session)
	{
		parent::__construct('player', $sanitizer, $session, 'player_filter');
		$this->currentParameters = array_merge($this->defaultParameters, $this->moduleParameters);

		$this->setDefaultForParameter(self::PARAMETER_SORT_COLUMN, self::PARAMETER_PLAYER_ID);
	}
}