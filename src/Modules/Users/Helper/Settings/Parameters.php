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

namespace App\Modules\Users\Helper\Settings;

use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Utils\FormParameters\BaseEditParameters;
use App\Framework\Utils\FormParameters\ScalarType;

class Parameters extends BaseEditParameters
{
	const string PARAMETER_USER_NAME  = 'username';
	const string PARAMETER_USER_EMAIL  = 'email';
	const string PARAMETER_USER_ID  = 'UID'; // the UID of the edited user

	/**
	 * @var array<string, array{scalar_type: ScalarType, default_value: string|int, parsed: bool}>
	 */
	protected array $moduleParameters = [
		self::PARAMETER_USER_ID  => ['scalar_type' => ScalarType::INT, 'default_value' => 0, 'parsed' => false],
		self::PARAMETER_USER_NAME  => ['scalar_type' => ScalarType::STRING, 'default_value' => '', 'parsed' => false],
		self::PARAMETER_USER_EMAIL => ['scalar_type' => ScalarType::STRING, 'default_value' => '', 'parsed' => false]
	];

	public function __construct(Sanitizer $sanitizer, Session $session)
	{
		parent::__construct('users', $sanitizer, $session);
		$this->currentParameters = array_merge($this->defaultParameters, $this->moduleParameters);
	}


}