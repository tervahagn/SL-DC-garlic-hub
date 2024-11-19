<?php

namespace Tests\Framework\Exceptions;

use App\Framework\Exceptions\CoreException;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class CoreExceptionTest extends TestCase
{
	#[Group('units')]
	public function testCoreExceptionConstructor(): void
	{
		$exception = new CoreException('Test Message', 100);

		$this->assertSame('Core', $exception->getModuleName());
		$this->assertSame('Test Message', $exception->getMessage());
		$this->assertSame(100, $exception->getCode());
		$this->assertNull($exception->getPrevious());
	}

	#[Group('units')]
	public function testCoreExceptionConstructorWithPrevious(): void
	{
		$previousException = new Exception('Previous Message');
		$exception = new CoreException('Test Message', 100, $previousException);

		$this->assertSame($previousException, $exception->getPrevious());
	}

}
