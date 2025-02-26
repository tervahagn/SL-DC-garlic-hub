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

namespace App\Modules\Playlists\FormHelper;

use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FormParameters\BaseEditParameters;
use App\Framework\Utils\FormParameters\ScalarType;

class SettingsParameters extends BaseEditParameters
{
	const string PARAMETER_PLAYLIST_ID        = 'playlist_id';
	const string PARAMETER_NAME               = 'playlist_name';
	const string PARAMETER_PLAYLIST_MODE      = 'playlist_mode';
	const string PARAMETER_TIME_LIMIT         = 'time_limit';
	const string PARAMETER_MULTIZONE          = 'multizone';

	protected array $moduleParameters = array(
		self::PARAMETER_NAME            => array('scalar_type'  => ScalarType::STRING,   'default_value' => '', 'parsed' => false)
	);

	public function __construct(Sanitizer $sanitizer, Session $session)
	{
		parent::__construct('playlists', $sanitizer, $session);
		$this->currentParameters = array_merge($this->defaultParameters, $this->moduleParameters);
	}

	/**
	 * @throws ModuleException
	 */
	public function addPlaylistMode(): void
	{
		$this->addParameter(self::PARAMETER_PLAYLIST_MODE, ScalarType::STRING, '');
	}

	/**
	 * @throws ModuleException
	 */
	public function addPlaylistId(): void
	{
		$this->addParameter(self::PARAMETER_PLAYLIST_ID, ScalarType::INT, 0);
	}

	/**
	 * @throws ModuleException
	 */
	public function addTimeLimit(): void
	{
		$this->addParameter(self::PARAMETER_TIME_LIMIT, ScalarType::INT, 0);
	}

	/**
	 * @throws ModuleException
	 */
	public function addMultizone(): void
	{
		$this->addParameter(self::PARAMETER_MULTIZONE, ScalarType::STRING_ARRAY, []);
	}



}