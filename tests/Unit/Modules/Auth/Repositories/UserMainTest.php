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

namespace Tests\Unit\Modules\Auth\Repositories;


use App\Framework\Exceptions\UserException;
use App\Modules\Auth\Entities\User;
use App\Modules\Auth\Repositories\UserMain;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class UserMainTest extends TestCase
{
	private Connection	 $connectionMock;
	private QueryBuilder $queryBuilderMock;
	private Result $resultMock;

	protected function setUp(): void
	{
		$this->connectionMock   = $this->createMock(Connection::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);
		$this->resultMock       = $this->createMock(Result::class);
	}


	/**
	 * @throws UserException
	 */
	#[Group('units')]
	public function testLoadUserByIdentifierForValidEmail()
	{
		$userMain = new UserMain($this->connectionMock);
		$identifier = 'test@example.com';
		$userData = [
			'UID' => 1,
			'username' => 'testuser',
			'email' => 'test@example.com',
			'password' => 'hashedpassword',
			'locale' => 'en_US',
			'company_id' => 123,
			'status' => 'active'
		];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')->with('*')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('from')->with('user_main')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->never())->method('join');
		$this->queryBuilderMock->expects($this->never())->method('groupBy');
		$this->queryBuilderMock->expects($this->never())->method('orderBy');
		$this->queryBuilderMock->expects($this->never())->method('setFirstResult');
		$this->queryBuilderMock->expects($this->never())->method('setMaxResults');

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);
		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn([$userData]);

		$user = $userMain->loadUserByIdentifier($identifier);

		$this->assertInstanceOf(User::class, $user);
		$this->assertEquals('testuser', $user->getUsername());
	}

	#[Group('units')]
	public function testLoadUserByIdentifierForValidUsername()
	{
		$userMain = new UserMain($this->connectionMock);
		$identifier = 'testuser';
		$userData = [
			'UID' => 1,
			'username' => 'testuser',
			'email' => 'test@example.com',
			'password' => 'hashedpassword',
			'locale' => 'en_US',
			'company_id' => 123,
			'status' => 'active'
		];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')->with('*')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('from')->with('user_main')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->never())->method('join');
		$this->queryBuilderMock->expects($this->never())->method('groupBy');
		$this->queryBuilderMock->expects($this->never())->method('orderBy');
		$this->queryBuilderMock->expects($this->never())->method('setFirstResult');
		$this->queryBuilderMock->expects($this->never())->method('setMaxResults');

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);
		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn([$userData]);

		$user = $userMain->loadUserByIdentifier($identifier);

		$this->assertInstanceOf(User::class, $user);
		$this->assertEquals('testuser', $user->getUsername());
	}

	#[Group('units')]
	public function testLoadUserByIdentifierThrowsExceptionForInvalidIdentifier()
	{
		$this->expectException(UserException::class);
		$this->expectExceptionMessage('User not found.');

		$userMain = new UserMain($this->connectionMock);

		$identifier = 'nonexistentuser';

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')->with('*')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('from')->with('user_main')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->never())->method('join');
		$this->queryBuilderMock->expects($this->never())->method('groupBy');
		$this->queryBuilderMock->expects($this->never())->method('orderBy');
		$this->queryBuilderMock->expects($this->never())->method('setFirstResult');
		$this->queryBuilderMock->expects($this->never())->method('setMaxResults');

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);
		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn([]);

		$userMain->loadUserByIdentifier($identifier);
	}
}
