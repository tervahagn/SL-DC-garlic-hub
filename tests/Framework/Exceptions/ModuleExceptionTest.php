<?php

namespace Tests\Framework\Exceptions;

use App\Framework\Exceptions\ModuleException;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ModuleExceptionTest extends TestCase
{
	#[Group('units')]
	public function testBaseExceptionConstructor(): void
	{
		$exception = new ModuleException('CustomModule', 'Base exception occurred', 100);

		$this->assertSame('CustomModule', $exception->getModuleName());
		$this->assertSame('Base exception occurred', $exception->getMessage());
		$this->assertSame(100, $exception->getCode());
		$this->assertNull($exception->getPrevious());
	}

	#[Group('units')]
	public function testBaseExceptionConstructorWithPrevious(): void
	{
		$previousException = new Exception('Previous error');
		$exception = new ModuleException('CustomModule', 'Base exception occurred', 100, $previousException);

		$this->assertSame('CustomModule', $exception->getModuleName());
		$this->assertSame('Base exception occurred', $exception->getMessage());
		$this->assertSame(100, $exception->getCode());
		$this->assertSame($previousException, $exception->getPrevious());
	}
}
