<?php

namespace Tests\Unit\Framework\User;

use App\Framework\User\UserEntity;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class UserEntityTest extends TestCase
{
	private UserEntity $userEntity;

	protected function setUp(): void
	{
		$this->userEntity = new UserEntity(['id' => '123', 'name' => 'John Doe'], [
				'email' => 'john@example.com',
				'phone' => '123456789'
			], ['logins' => 42, 'last_login' => '2024-12-19'], ['password' => 'hashed_password'], [
				'role' => 'admin',
				'permissions' => [
					'read',
					'write'
				]
			], ['status' => 'VIP']);
	}

	#[Group('units')]
	public function testGetMain(): void
	{
		$this->assertSame(['id' => '123', 'name' => 'John Doe'], $this->userEntity->getMain());
	}

	#[Group('units')]
	public function testGetContact(): void
	{
		$this->assertSame(['email' => 'john@example.com', 'phone' => '123456789'], $this->userEntity->getContact());
	}

	#[Group('units')]
	public function testGetStats(): void
	{
		$this->assertSame(['logins' => 42, 'last_login' => '2024-12-19'], $this->userEntity->getStats());
	}

	#[Group('units')]
	public function testGetSecurity(): void
	{
		$this->assertSame(['password' => 'hashed_password'], $this->userEntity->getSecurity());
	}

	#[Group('units')]
	public function testGetAcl(): void
	{
		$this->assertSame(['role' => 'admin', 'permissions' => ['read', 'write']], $this->userEntity->getAcl());
	}

	#[Group('units')]
	public function testGetVip(): void
	{
		$this->assertSame(['status' => 'VIP'], $this->userEntity->getVip());
	}

	#[Group('units')]
	public function testGetIdentifier(): void
	{
		$this->assertSame('123', $this->userEntity->getIdentifier());
	}
}