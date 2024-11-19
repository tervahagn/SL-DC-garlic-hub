<?php

namespace Tests\Framework\Exceptions;

use App\Framework\Exceptions\FrameworkException;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class FrameworkExceptionTest extends TestCase
{
	#[Group('units')]
	public function testFrameworkExceptionConstructor(): void
	{
		$exception = new FrameworkException('Framework error occurred', 500);

		$this->assertSame('Framework', $exception->getModuleName());
		$this->assertSame('Framework error occurred', $exception->getMessage());
		$this->assertSame(500, $exception->getCode());
		$this->assertNull($exception->getPrevious());
	}

	#[Group('units')]
	public function testFrameworkExceptionConstructorWithPrevious(): void
	{
		$previousException = new Exception('Previous framework error');
		$exception = new FrameworkException('Framework error occurred', 500, $previousException);

		$this->assertSame('Framework', $exception->getModuleName());
		$this->assertSame('Framework error occurred', $exception->getMessage());
		$this->assertSame(500, $exception->getCode());
		$this->assertSame($previousException, $exception->getPrevious());
	}
}
