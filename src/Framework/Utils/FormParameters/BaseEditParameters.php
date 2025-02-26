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

namespace App\Framework\Utils\FormParameters;

use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Exceptions\ModuleException;

class BaseEditParameters extends BaseParameters
{
	public const string PARAMETER_CSRF_TOKEN  = 'csrf_token';
	public const string PARAMETER_UID  = 'UID';

	protected array $defaultParameters = array(
		self::PARAMETER_CSRF_TOKEN  => array('scalar_type'  => ScalarType::STRING, 'default_value' => '', 'parsed' => false)
	);

	public function __construct(string $moduleName, Sanitizer $sanitizer, Session $session)
	{
		parent::__construct($moduleName, $sanitizer, $session);
	}

	/**
	 * @throws ModuleException
	 */
	public function addUID(): void
	{
		$this->addParameter(self::PARAMETER_UID, ScalarType::INT, 0);
	}

	/**
	 * Remark: This violates SRP. If we have more security checks we
	 * will create an own class.
	 *
	 * @throws ModuleException
	 */
	public function checkCsrfToken(): void
	{
		if (!isset($this->currentParameters[self::PARAMETER_CSRF_TOKEN]))
			throw new ModuleException($this->moduleName,'CSRF token not set in parameters');

		if (!$this->session->exists(self::PARAMETER_CSRF_TOKEN))
			throw new ModuleException($this->moduleName,'CSRF token not set in session');

		if ($this->session->get(self::PARAMETER_CSRF_TOKEN) !== $this->currentParameters[self::PARAMETER_CSRF_TOKEN]['value'])
			throw new ModuleException($this->moduleName,'CSRF token mismatch');
	}
}