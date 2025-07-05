<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);

namespace App\Framework\Utils\FormParameters;

use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Exceptions\ModuleException;

abstract class BaseEditParameters extends BaseParameters
{
	protected Session $session;
	public const string PARAMETER_CSRF_TOKEN  = 'csrf_token';

	/** @var array<string, array{scalar_type: ScalarType, default_value: mixed, parsed: bool, value?:mixed}> */
	protected array $defaultParameters = array(
		self::PARAMETER_CSRF_TOKEN  => array('scalar_type'  => ScalarType::STRING, 'default_value' => '', 'parsed' => false)
	);

	public function __construct(string $moduleName, Sanitizer $sanitizer, Session $session)
	{
		$this->session = $session;
		parent::__construct($moduleName, $sanitizer);
	}

	/**
	 * Remark: This violates SRP. If we have more security checks, we
	 * will create our own class.
	 *
	 * @throws ModuleException
	 */
	public function checkCsrfToken(): void
	{
		if (!isset($this->currentParameters[self::PARAMETER_CSRF_TOKEN]))
			throw new ModuleException($this->moduleName,'CSRF token not set in parameters');

		if (!$this->session->exists(self::PARAMETER_CSRF_TOKEN))
			throw new ModuleException($this->moduleName,'CSRF token not set in session');

		if (!array_key_exists('value', $this->currentParameters[self::PARAMETER_CSRF_TOKEN]))
			throw new ModuleException($this->moduleName,'CSRF token mismatch - No value set in parameters');

		if ($this->session->get(self::PARAMETER_CSRF_TOKEN) !== $this->currentParameters[self::PARAMETER_CSRF_TOKEN]['value'])
			throw new ModuleException($this->moduleName,'CSRF token mismatch');
	}
}