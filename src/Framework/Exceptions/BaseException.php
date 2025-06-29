<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

namespace App\Framework\Exceptions;

use Exception;

/**
 * BaseException
 *
 * Extended Exception class for managing additional details like module name and providing
 * exception details in array and string formats.
 *
 * @package App\Framework\Exceptions
 */
class BaseException extends Exception
{
	/**
	 * @var string The name of the module where the exception occurred.
	 */
	protected string $module_name;

	/**
	 * Sets the module name where the exception occurred.
	 *
	 * @param string $module_name The module name.
	 * @return $this
	 */
	public function setModuleName(string $module_name): self
	{
		$this->module_name = $module_name;
		return $this;
	}

	/**
	 * Gets the module name where the exception occurred.
	 *
	 * @return string The module name.
	 */
	public function getModuleName(): string
	{
		return $this->module_name;
	}

	/**
	 * Returns exception details as an associative array.
	 *
	 * @return array{module_name: string, message: string, code: int|mixed, file: string, line: int, trace: string}
	 * - 'module_name': The module name.
	 * - 'message': The exception message.
	 * - 'code': The exception code.
	 * - 'file': The file where the exception occurred.
	 * - 'line': The line number where the exception occurred.
	 * - 'trace': The exception stack trace.
	 */
	public function getDetails(): array
	{
		return [
			'module_name' => $this->getModuleName(),
			'message'     => $this->getMessage(),
			'code'        => $this->getCode(),
			'file'        => $this->getFile(),
			'line'        => $this->getLine(),
			'trace'       => $this->getTraceAsString(),
		];
	}

	/**
	 * Returns exception details as a formatted string.
	 *
	 * @return string The formatted exception details.
	 */
	public function getDetailsAsString(): string
	{
		$details = $this->getDetails();
		return "Module: {$details['module_name']}\n"
			. "Message: {$details['message']}\n"
			. "Code: {$details['code']}\n"
			. "File: {$details['file']} (Line {$details['line']})\n"
			. "Trace:\n{$details['trace']}\n";
	}
}
