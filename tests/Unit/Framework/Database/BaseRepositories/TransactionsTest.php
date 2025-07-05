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
declare(strict_types=1);


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
		parent::setUp();
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
