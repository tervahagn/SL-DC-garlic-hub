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

namespace App\Framework\Exceptions;

use Exception;

/**
 * ModuleException
 *
 * Exception for errors in specific modules, with dynamic module name assignment.
 */
class ModuleException extends BaseException
{
	/**
	 * ModuleException constructor.
	 *
	 * Sets the module name dynamically and initializes the exception.
	 *
	 * @param string          $module_name The name of the module where the exception occurred.
	 * @param string          $message     The exception message.
	 * @param int             $code        The exception code.
	 * @param Exception|null $previous    Previous exception for chaining.
	 */
	public function __construct(string $module_name, $message = '', $code = 0, Exception $previous = null)
	{
		$this->setModuleName($module_name);
		parent::__construct($message, $code, $previous);
	}
}
