<?php

namespace Tests\Commands;

use App\Commands\MigrateCommand;
use App\Framework\Migration\MigrateDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class MigrateCommandTest extends TestCase
{
	private MigrateDatabase $migrationMock;
	private array $paths;
	private MigrateCommand $command;

	protected function setUp(): void
	{
		$this->migrationMock = $this->createMock(MigrateDatabase::class);
		$this->paths = [
			'migrationDir' => '/var/migrations'
		];

		$this->command = new MigrateCommand($this->migrationMock, $this->paths);
	}

	#[Group('units')]
	public function testExecuteSuccess(): void
	{
		$this->migrationMock
			->expects($this->once())
			->method('setSilentOutput')
			->with(true);

		$this->migrationMock
			->expects($this->once())
			->method('setMigrationFilePath')
			->with('/var/migrations/EDGE/');

		$this->migrationMock
			->expects($this->once())
			->method('execute');

		$_ENV['APP_PLATFORM_EDITION'] = 'EDGE';

		$input = new ArrayInput([]);
		$output = new BufferedOutput();

		$statusCode = $this->command->run($input, $output);

		$this->assertEquals(0, $statusCode);
		$this->assertStringContainsString('Migration succeed.', $output->fetch());
	}

	#[Group('units')]
	public function testExecuteFailure(): void
	{
		$this->migrationMock
			->expects($this->once())
			->method('setSilentOutput')
			->with(true);

		$this->migrationMock
			->expects($this->once())
			->method('setMigrationFilePath')
			->with('/var/migrations/EDGE/');

		$this->migrationMock
			->expects($this->once())
			->method('execute')
			->willThrowException(new \Exception('Mocked failure'));

		$_ENV['APP_PLATFORM_EDITION'] = 'EDGE';

		$input = new ArrayInput([]);
		$output = new BufferedOutput();

		$statusCode = $this->command->run($input, $output);

		$this->assertEquals(1, $statusCode);
		$this->assertStringContainsString('Migration failed: Mocked failure', $output->fetch());
	}

	#[Group('units')]
	public function testMissingEditionEnvironmentVariable(): void
	{
		unset($_ENV['APP_PLATFORM_EDITION']);

		$input = new ArrayInput([]);
		$output = new BufferedOutput();

		$this->expectException(\ErrorException::class);

		$this->command->run($input, $output);
	}
}