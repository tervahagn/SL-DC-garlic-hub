<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
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
