<?php

namespace Tests\Unit\Framework\User\Edge;

use App\Framework\User\Edge\UserMainRepository;
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
			->with('UID, company_id, status, locale')
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
