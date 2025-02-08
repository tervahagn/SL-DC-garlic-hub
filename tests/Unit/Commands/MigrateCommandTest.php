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


namespace Tests\Unit\Commands;

use App\Commands\MigrateCommand;
use App\Framework\Database\Migration\Runner;
use App\Framework\Exceptions\DatabaseException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommandTest extends TestCase
{
	private Runner $runnerMock;
	private InputInterface $inputMock;
	private OutputInterface $outputMock;
	private MigrateCommand $command;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		// Initialisiere Mocks für alle Tests
		$this->runnerMock = $this->createMock(Runner::class);
		$this->inputMock = $this->createMock(InputInterface::class);
		$this->outputMock = $this->createMock(OutputInterface::class);

		// Command mit Runner-Mock erstellen
		$this->command = new MigrateCommand($this->runnerMock);
	}

	/**
	 * @throws ExceptionInterface
	 */
	#[Group('units')]
	public function testMigrationSuccess(): void
	{
		$this->runnerMock->expects($this->once())
			->method('execute')
			->with(null); // Default argument
		$this->runnerMock->method('isApplied')
			->willReturn(true);

		$this->inputMock->method('getArgument')
			->with('version')
			->willReturn(null);
		$this->inputMock->method('getOption')
			->with('rollback')
			->willReturn(false);

		$this->outputMock->expects($this->once())
			->method('writeln')
			->with('<info>Migration succeed.</info>');

		$result = $this->command->run($this->inputMock, $this->outputMock);
		$this->assertSame(0, $result); // Command::SUCCESS
	}

	/**
	 * @throws ExceptionInterface
	 */
	#[Group('units')]
	public function testRollbackSuccess(): void
	{
		$this->runnerMock->expects($this->once())
			->method('rollback')
			->with(null);
		$this->runnerMock->method('isApplied')
			->willReturn(true);

		$this->inputMock->method('getArgument')
			->with('version')
			->willReturn(null);
		$this->inputMock->method('getOption')
			->with('rollback')
			->willReturn(true);

		$this->outputMock->expects($this->once())
			->method('writeln')
			->with('<info>Migration succeed.</info>');

		$result = $this->command->run($this->inputMock, $this->outputMock);
		$this->assertSame(0, $result); // Command::SUCCESS
	}

	/**
	 * @throws ExceptionInterface
	 */
	#[Group('units')]
	public function testMigrationFailsWithException(): void
	{
		$this->runnerMock->expects($this->once())
			->method('execute')
			->willThrowException(new DatabaseException('Test Exception'));

		$this->inputMock->method('getArgument')
			->with('version')
			->willReturn(null);
		$this->inputMock->method('getOption')
			->with('rollback')
			->willReturn(false);

		$this->outputMock->expects($this->once())
			->method('writeln')
			->with('<error>Migration failed: Test Exception</error>');

		$result = $this->command->run($this->inputMock, $this->outputMock);

		$this->assertSame(1, $result); // Command::FAILURE
	}

	/**
	 * @throws ExceptionInterface
	 */
	#[Group('units')]
	public function testNoMigrationsFound(): void
	{
		$this->runnerMock->expects($this->once())
			->method('execute')
			->with(null);
		$this->runnerMock->method('isApplied')
			->willReturn(false);

		$this->inputMock->method('getArgument')
			->with('version')
			->willReturn(null);
		$this->inputMock->method('getOption')
			->with('rollback')
			->willReturn(false);

		$this->outputMock->expects($this->once())
			->method('writeln')
			->with('<comment>No migrations found to apply.</comment>');

		$result = $this->command->run($this->inputMock, $this->outputMock);
		$this->assertSame(0, $result); // Command::SUCCESS
	}
}
