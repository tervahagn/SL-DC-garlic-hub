<?php

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
	 * @return array Exception details
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
