<?php

namespace Tests\Unit\Modules\Auth\Repositories;

use App\Framework\Database\DBHandler;
use App\Framework\Database\Helpers\DataPreparer;
use App\Framework\Database\QueryBuilder;
use App\Framework\Exceptions\UserException;
use App\Modules\Auth\Entities\User;
use App\Modules\Auth\Repositories\UserMain;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class UserMainTest extends TestCase
{
	private DBHandler	 $dbhMock;
	private QueryBuilder $queryBuilderMock;
	private DataPreparer $dataPreparerMock;

	protected function setUp(): void
	{
		$this->dbhMock = $this->createMock(DBHandler::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);
		$this->dataPreparerMock = $this->createMock(DataPreparer::class);
	}


	#[Group('units')]
	public function testLoadUserByIdentifierForValidEmail()
	{
		$userMain = new UserMain($this->dbhMock, $this->queryBuilderMock, $this->dataPreparerMock);
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

		$this->queryBuilderMock->expects($this->once())
							   ->method('buildSelectQuery')
							   ->willReturn('query');
		$this->dbhMock->expects($this->once())
					  ->method('select')
					  ->with('query')
					  ->willReturn([$userData]);

		$user = $userMain->loadUserByIdentifier($identifier);

		$this->assertInstanceOf(User::class, $user);
		$this->assertEquals('testuser', $user->getUsername());
	}

	#[Group('units')]
	public function testLoadUserByIdentifierForValidUsername()
	{
		$userMain = new UserMain($this->dbhMock, $this->queryBuilderMock, $this->dataPreparerMock);
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

		$this->queryBuilderMock->expects($this->once())
							   ->method('buildSelectQuery')
							   ->willReturn('query');
		$this->dbhMock->expects($this->once())
					  ->method('select')
					  ->with('query')
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

		$userMain = new UserMain($this->dbhMock, $this->queryBuilderMock, $this->dataPreparerMock);

		$identifier = 'nonexistentuser';

		$this->queryBuilderMock->expects($this->once())
							   ->method('buildSelectQuery')
							   ->willReturn('query');

		$this->dbhMock->expects($this->once())
					  ->method('select')
					  ->with('query')
					  ->willReturn([]);

		$userMain->loadUserByIdentifier($identifier);
	}
}
