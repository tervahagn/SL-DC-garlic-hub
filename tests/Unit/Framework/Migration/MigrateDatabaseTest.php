<?php

namespace Tests\Unit\Framework\Migration;

use App\Framework\Migration\MigrateDatabase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use PHPMock;

class MigrateDatabaseTest extends TestCase
{
	private $filesystemMock;
	private Connection $connectionMock;
	private QueryBuilder $queryBuilderMock;
	private AbstractSchemaManager $schemaManagerMock;
	private MigrateDatabase $migrateDatabase;

	protected function setUp(): void
	{
		$this->connectionMock    = $this->createMock(Connection::class);
		$this->filesystemMock    = $this->createMock(FilesystemOperator::class);
		$this->queryBuilderMock  = $this->createMock(QueryBuilder::class);
		$this->schemaManagerMock = $this->createMock(AbstractSchemaManager::class);
		$this->schemaManagerMock = $this->createMock(AbstractSchemaManager::class);
		$this->resultMock        = $this->createMock(Result::class);

		$this->connectionMock->method('createQueryBuilder')->willReturn($this->queryBuilderMock);
		$this->connectionMock->method('createSchemaManager')->willReturn($this->schemaManagerMock);

		$this->migrateDatabase = $this->getMockBuilder(MigrateDatabase::class)
			->setConstructorArgs([$this->connectionMock, $this->filesystemMock])
			->onlyMethods(['getFileName'])
			->getMock();

		$this->migrateDatabase->method('getFileName')->willReturnCallback(function ($number, $direction, $name) {
			// Bestimme die Erweiterung anhand des Namens
			$extension = str_contains($name, 'comments') ? 'php' : 'sql';
			$formattedNumber = str_pad($number, 3, '0', STR_PAD_LEFT);
			return "/migrations/{$formattedNumber}_{$name}.{$direction}.{$extension}";
		});

		// Initialisiere den Migrationspfad
		$this->migrateDatabase->setMigrationFilePath('/migrations');

	}

	#[Group('units')]
	public function testHasMigrationTableWhenTableExists()
	{
		$this->schemaManagerMock->method('listTables')->willReturn(['_migration_version']);
		$this->assertTrue($this->migrateDatabase->hasMigrationTable());
	}

	#[Group('units')]
	public function testHasMigrationTableWhenTableDoesNotExist()
	{
		$this->schemaManagerMock->method('listTables')->willReturn([]);
		$this->assertFalse($this->migrateDatabase->hasMigrationTable());
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCreateMigrationTable()
	{
		$this->connectionMock->expects($this->once())
			->method('executeStatement')
			->with($this->stringContains('CREATE TABLE IF NOT EXISTS '. MigrateDatabase::MIGRATION_TABLE_NAME));

		$this->migrateDatabase->createMigrationTable();
	}

	#[Group('units')]
	public function testGetMigrationVersion()
	{
		$this->queryBuilderMock->expects($this->once())
			->method('select')
			->with('version')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())
			->method('from')
			->with('_migration_version')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())
			->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())
			->method('fetchOne')
			->willReturn(5);

		$this->assertEquals(5, $this->migrateDatabase->getMigrationVersion());
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testUpdateMigrationVersion()
	{
		$this->queryBuilderMock->expects($this->once())
			->method('update')
			->with('_migration_version')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())
			->method('set')
			->with('version', ':value')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())
			->method('where')
			->with('version > :threshold')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->exactly(2))
			->method('setParameter')
			->willReturnCallback(function ($key, $value) {
				static $calls = [
					['value', 10],
					['threshold', 0]
				];

				$expected = array_shift($calls);
				$this->assertEquals($expected[0], $key);
				$this->assertEquals($expected[1], $value);

				return $this->queryBuilderMock;
			});

		$this->queryBuilderMock->expects($this->once())
			->method('executeStatement');

		$this->migrateDatabase->updateMigrationVersion(10);
	}

	/**
	 * @throws \Exception
	 */
	#[Group('units')]
	public function testGetAvailableMigrations()
	{
		$files = new DirectoryListing([
			new FileAttributes('001_create_users.up.sql'),
			new FileAttributes('002_create_posts.down.sql'),
			new FileAttributes('003_create_comments.up.php'),
		]);

		$expectedMigrations = [
			1 => ['up' => '/migrations/001_create_users.up.sql'],
			2 => ['down' => '/migrations/002_create_posts.down.sql'],
			3 => [
				'up' => '/migrations/003_create_comments.up.php',
				'down' => '/migrations/003_create_comments.up.php',
			],
		];

		$this->filesystemMock->method('listContents')
			->with('/migrations')
			->willReturn($files);

		list($highest, $migrations) = $this->migrateDatabase->getAvailableMigrations();

		$this->assertEquals(3, $highest, 'Highest migration number should be 3');
		$this->assertEquals($expectedMigrations, $migrations, 'Migrations array does not match expected structure');
	}

	#[Group('units')]
	public function testGetAvailableMigrationsWithInvalidFileName()
	{

		$files = new DirectoryListing([
			new FileAttributes('invalid_file_name.sql')
		]);

		$this->filesystemMock->method('listContents')
			->with('/migrations')
			->willReturn($files);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Wrong migration script name: [invalid_file_name.sql]');

		// Methode aufrufen, um die Exception zu testen
		$this->migrateDatabase->getAvailableMigrations();
	}

	#[Group('units')]
	public function testGetAvailableMigrationsWithDuplicateMigration()
	{
		$files = [
			['type' => 'file', 'path' => '001_create_users.up.sql'],
			['type' => 'file', 'path' => '001_create_users.up.sql'],
		];
		$files = new DirectoryListing([
			new FileAttributes('001_create_users.up.sql'),
			new FileAttributes('001_create_users.up.sql')
		]);

		$this->filesystemMock->method('listContents')
			->with('/migrations')
			->willReturn($files);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Migration [1 => up] doubled!');

		// Methode aufrufen, um die Exception zu testen
		$this->migrateDatabase->getAvailableMigrations();
	}
}
