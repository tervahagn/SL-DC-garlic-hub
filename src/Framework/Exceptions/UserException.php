<?php

namespace App\Framework\Exceptions;

use Exception;

/**
 * UserException
 *
 * Exception for user-related errors.
 */
class UserException extends BaseException
{
	/**
	 * UserException constructor.
	 *
	 * Sets the module name to "User" and initializes the exception.
	 *
	 * @param string         $message  The exception message.
	 * @param int            $code     The exception code.
	 * @param Exception|null $previous Previous exception for chaining.
	 */
	public function __construct(string $message, int $code = 0, Exception $previous = null)
	{
		$this->setModuleName('User');
		parent::__construct($message, $code, $previous);
	}
}
