<?php

namespace Tests\Unit\Framework\Database\BaseRepositories;

use App\Framework\Database\BaseRepositories\Traits\TransactionsTrait;
use App\Framework\Database\BaseRepositories\Transactions;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransactionsTest extends TestCase
{

	private Connection&MockObject $connectionMock;
	private Transactions $transactions;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->connectionMock = $this->createMock(Connection::class);
		$this->transactions = new Transactions($this->connectionMock);
	}

	#[Group('units')]
	public function testBeginTransaction(): void
	{
		$this->connectionMock
			->expects($this->once())
			->method('beginTransaction');

		$this->transactions->begin();
	}

	#[Group('units')]
	public function testIsTransactionActive(): void
	{
		$this->connectionMock
			->expects($this->once())
			->method('isTransactionActive')
			->willReturn(true);

		$this->assertTrue($this->transactions->isActive());
	}

	#[Group('units')]
	public function testCommitTransaction(): void
	{
		$this->connectionMock
			->expects($this->once())
			->method('commit');

		$this->transactions->commit();
	}

	#[Group('units')]
	public function testRollBackTransaction(): void
	{
		$this->connectionMock
			->expects($this->once())
			->method('rollBack');

		$this->transactions->rollBack();
	}


}
