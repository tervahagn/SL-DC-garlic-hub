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

namespace Tests\Unit\Framework\Users\Repositories\Edge;

use App\Framework\Users\Repositories\Edge\UserMainRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class UserMainRepositoryTest extends TestCase
{
	private Connection	 $connectionMock;
	private QueryBuilder $queryBuilderMock;
	private Result $resultMock;

	/**
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$this->connectionMock   = $this->createMock(Connection::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);
		$this->resultMock       = $this->createMock(Result::class);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFindByIdentifierForValidEmail()
	{
		$userMain = new UserMainRepository($this->connectionMock);
		$identifier = 'test@example.com';
		$userData = [
			'UID' => 1,
			'password' => 'hashed_password',
			'locale' => 'en_US',
			'status' => 'active'
		];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')->with('UID, password, locale, status, company_id')
			->willReturn($this->queryBuilderMock); // needed in from because of chained methods
		$this->queryBuilderMock->expects($this->once())->method('from')->with('user_main');
		$this->queryBuilderMock->expects($this->once())->method('where')->with('email = :identifier');
		$this->queryBuilderMock->expects($this->once())->method('setParameter')->with('identifier', $identifier);
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAssociative')
			->willReturn($userData);

		$this->assertEquals($userData, $userMain->findByIdentifier($identifier));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testLoadUserByIdentifierForValidUsername()
	{
		$userMain = new UserMainRepository($this->connectionMock);
		$identifier = 'testuser';
		$userData = [
			'UID' => 1,
			'password' => 'hashed_password',
			'locale' => 'en_US',
			'company_id' => 123,
			'status' => 'active'
		];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')->with('UID, password, locale, status, company_id')
			->willReturn($this->queryBuilderMock); // needed in from because of chained methods
		$this->queryBuilderMock->expects($this->once())->method('from')->with('user_main');
		$this->queryBuilderMock->expects($this->once())->method('where')->with('username = :identifier');
		$this->queryBuilderMock->expects($this->once())->method('setParameter')->with('identifier', $identifier);
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAssociative')
			->willReturn($userData);

		$this->assertEquals($userData, $userMain->findByIdentifier($identifier));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFindByIdReturnsResults(): void
	{
		$userMain = new UserMainRepository($this->connectionMock);
		$UID = 123;
		$userData = [
			['UID' => 123, 'company_id' => 1, 'status' => 'active', 'locale' => 'en_US'],
		];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->method('select')
			->with('UID, company_id, status, locale, email, username')
			->willReturnSelf();

		$this->queryBuilderMock->method('from')->with('user_main')
			->willReturnSelf();

		$this->queryBuilderMock->method('where')->with('UID = :id')
			->willReturnSelf();

		$this->queryBuilderMock->method('setParameter')
			->with('id', $UID);

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAssociative')
			->willReturn($userData);


		$result = $userMain->findById($UID);
		$this->assertEquals($userData, $result);
	}

}
