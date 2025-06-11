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

namespace Tests\Unit\Modules\Users\Repositories\Edge;

use App\Framework\Utils\FormParameters\BaseFilterParametersInterface;
use App\Modules\Users\Repositories\Edge\UserMainRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserMainRepositoryTest extends TestCase
{
	private readonly Connection&MockObject	 $connectionMock;
	private readonly QueryBuilder&MockObject $queryBuilderMock;
	private readonly Result&MockObject $resultMock;
	private readonly UserMainRepository $userMain;

	/**
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$this->connectionMock   = $this->createMock(Connection::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);
		$this->resultMock       = $this->createMock(Result::class);

		$this->userMain = new UserMainRepository($this->connectionMock);

	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFindByIdentifierForValidEmail()
	{
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

		$this->assertEquals($userData, $this->userMain->findByIdentifier($identifier));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testLoadUserByIdentifierForValidUsername()
	{
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

		$this->assertEquals($userData, $this->userMain->findByIdentifier($identifier));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFindByIdReturnsResults(): void
	{
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


		$result = $this->userMain->findById($UID);
		$this->assertEquals($userData, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testPrepareMethods(): void
	{
		$fields = [
			'username' => ['value' => 'johndoe'],
			'from_status' => ['value' => 3],
			BaseFilterParametersInterface::PARAMETER_ELEMENTS_PAGE => ['value' => 0],
			BaseFilterParametersInterface::PARAMETER_ELEMENTS_PER_PAGE => ['value' => 10],
		];
		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')
			->with('user_main.*')->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('from')
			->with('user_main')->willReturnSelf();
		$this->queryBuilderMock->expects($this->never())->method('leftJoin');
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')->willReturn($this->resultMock);

		$this->queryBuilderMock->expects($this->exactly(2))->method('andWhere')
			->willReturnMap([
					['user_main.username LIKE :user_mainusername', $this->queryBuilderMock],
					['status >= :status', $this->queryBuilderMock]
				]
			);
		$this->queryBuilderMock->expects($this->exactly(2))->method('setParameter')
			->willReturnMap([
					['user_mainusername', '%johndoe%', $this->queryBuilderMock],
					['status', 3, $this->queryBuilderMock]
				]
			);

		$this->queryBuilderMock->expects($this->never())->method('addOrderBy');

		$expectedResults = [['id' => 1, 'name' => 'John Doe']];
		$this->resultMock->expects($this->once())->method('fetchAllAssociative')->willReturn($expectedResults);

		$result = $this->userMain->findAllFiltered($fields);
		$this->assertSame($expectedResults, $result);
	}

}
