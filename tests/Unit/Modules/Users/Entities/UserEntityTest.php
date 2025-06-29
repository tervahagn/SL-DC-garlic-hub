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

namespace Tests\Unit\Modules\Users\Entities;

use App\Modules\Profile\Entities\UserEntity;
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