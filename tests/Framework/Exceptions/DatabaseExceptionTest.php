<?php

namespace Tests\Framework\Exceptions;

use App\Framework\Exceptions\DatabaseException;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class DatabaseExceptionTest extends TestCase
{
	#[Group('units')]
	public function testDatabaseExceptionConstructor(): void
	{
		$exception = new DatabaseException('Database error occurred', 200);

		$this->assertSame('DB', $exception->getModuleName());
		$this->assertSame('Database error occurred', $exception->getMessage());
		$this->assertSame(200, $exception->getCode());
		$this->assertNull($exception->getPrevious());
	}

	#[Group('units')]
	public function testDatabaseExceptionConstructorWithPrevious(): void
	{
		$previousException = new Exception('Previous error');
		$exception = new DatabaseException('Database error occurred', 200, $previousException);

		$this->assertSame('DB', $exception->getModuleName());
		$this->assertSame('Database error occurred', $exception->getMessage());
		$this->assertSame(200, $exception->getCode());
		$this->assertSame($previousException, $exception->getPrevious());
	}
}
