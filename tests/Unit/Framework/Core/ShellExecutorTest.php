<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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


namespace Tests\Unit\Framework\Core;

use App\Framework\Core\ShellExecutor;
use App\Framework\Exceptions\CoreException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ShellExecutorTest extends TestCase
{
	private ShellExecutor $executor;
	private LoggerInterface $loggerMock;

	protected function setUp(): void
	{
		$this->loggerMock = $this->createMock(LoggerInterface::class);
		$this->executor   = new ShellExecutor($this->loggerMock);
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testExecuteWithValidCommand()
	{
		$this->executor->setCommand('echo "Hello, World!"');
		$result = $this->executor->execute();

		$this->assertEquals(0, $result['exit_code']);
		$this->assertEquals('Hello, World!', $result['output']);
	}

	#[Group('units')]
	public function testExecuteWithoutCommandThrowsException()
	{
		$this->expectException(CoreException::class);
		$this->executor->execute();
	}

	#[Group('units')]
	public function testLoggerCalledOnError()
	{
		$this->loggerMock
			->expects($this->once())
			->method('error')
			->with($this->stringContains('Command failed'));

		$this->executor->setCommand('hurz');
		$this->executor->execute();
	}
}
