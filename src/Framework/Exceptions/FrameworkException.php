<?php

namespace App\Framework\Exceptions;

use Exception;

/**
 * FrameworkException
 *
 * Exception for framework-related errors.
 */
class FrameworkException extends BaseException
{
	/**
	 * FrameworkException constructor.
	 *
	 * Sets the module name to "Framework" and initializes the exception.
	 *
	 * @param string          $message  The exception message.
	 * @param int             $code     The exception code.
	 * @param Exception|null $previous Previous exception for chaining.
	 */
	public function __construct(string $message, int $code = 0, Exception $previous = null)
	{
		$this->setModuleName('Framework');
		parent::__construct($message, $code, $previous);
	}
}
