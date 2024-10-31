<?php

namespace App\Framework\Exceptions;

use Exception;

/**
 * DatabaseException
 *
 * Exception for database-related errors.
 */
class DatabaseException extends BaseException
{
	/**
	 * DatabaseException constructor.
	 *
	 * Sets the module name to "DB" and initializes the exception.
	 *
	 * @param string          $message  The exception message.
	 * @param int             $code     The exception code.
	 * @param Exception|null $previous Previous exception for chaining.
	 */
	public function __construct(string $message, int $code = 0, Exception $previous = null)
	{
		$this->setModuleName('DB');
		parent::__construct($message, $code, $previous);
	}
}
