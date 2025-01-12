<?php

namespace Tests\Unit\Framework\Database\BaseRepositories;

use App\Framework\Database\BaseRepositories\TransactionsTrait;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class TransactionsTraitTest extends TestCase
{
	private Connection $connectionMock;
	private object $traitObject;

	protected function setUp(): void
	{
		$this->connectionMock = $this->createMock(Connection::class);
		$this->traitObject = new class($this->connectionMock) {
			use TransactionsTrait;

			public function __construct(private Connection $connection)
			{
			}

			public function __get($name)
			{
				return $this->$name;
			}
		};
	}

	#[Group('units')]
	public function testBeginTransaction(): void
	{
		$this->connectionMock
			->expects($this->once())
			->method('beginTransaction');

		$this->traitObject->beginTransaction();
	}

	#[Group('units')]
	public function testIsTransactionActive(): void
	{
		$this->connectionMock
			->expects($this->once())
			->method('isTransactionActive')
			->willReturn(true);

		$this->assertTrue($this->traitObject->isTransactionActive());
	}

	#[Group('units')]
	public function testCommitTransaction(): void
	{
		$this->connectionMock
			->expects($this->once())
			->method('commit');

		$this->traitObject->commitTransaction();
	}

	#[Group('units')]
	public function testRollBackTransaction(): void
	{
		$this->connectionMock
			->expects($this->once())
			->method('rollBack');

		$this->traitObject->rollBackTransaction();
	}
}
