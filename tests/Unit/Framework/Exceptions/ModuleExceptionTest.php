<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace Tests\Unit\Framework\Exceptions;

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
