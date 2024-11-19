<?php

namespace Tests\Framework\Exceptions;

use App\Framework\Exceptions\UserException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class UserExceptionTest extends TestCase
{
	#[Group('units')]
	public function testUserExceptionConstructor(): void
	{
		$exception = new UserException('User error occurred', 400);

		$this->assertSame('User', $exception->getModuleName());
		$this->assertSame('User error occurred', $exception->getMessage());
		$this->assertSame(400, $exception->getCode());
		$this->assertNull($exception->getPrevious());
	}

	#[Group('units')]
	public function testUserExceptionConstructorWithPrevious(): void
	{
		$previousException = new \Exception('Previous user error');
		$exception = new UserException('User error occurred', 400, $previousException);

		$this->assertSame('User', $exception->getModuleName());
		$this->assertSame('User error occurred', $exception->getMessage());
		$this->assertSame(400, $exception->getCode());
		$this->assertSame($previousException, $exception->getPrevious());
	}
}
