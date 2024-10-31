<?php

namespace App\Framework\Exceptions;

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
	 * @param \Exception|null $previous    Previous exception for chaining.
	 */
	public function __construct(string $module_name, $message = '', $code = 0, \Exception $previous = null)
	{
		$this->setModuleName($module_name);
		parent::__construct($message, $code, $previous);
	}
}
