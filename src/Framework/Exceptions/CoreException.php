<?php

namespace App\Framework\Exceptions;

/**
 * CoreException
 *
 * Specialized exception for core-related errors.
 */
class CoreException extends BaseException
{
	/**
	 * CoreException constructor.
	 *
	 * Sets the module name to "Core" and initializes the exception.
	 *
	 * @param string          $message  The exception message.
	 * @param int             $code     The exception code.
	 * @param \Exception|null $previous Previous exception for chaining.
	 */
	public function __construct(string $message, int $code = 0, \Exception $previous = null)
	{
		$this->setModuleName('Core');
		parent::__construct($message, $code, $previous);
	}
}
