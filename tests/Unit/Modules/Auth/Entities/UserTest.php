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

namespace Tests\Unit\Modules\Auth\Entities;

use App\Modules\Auth\Entities\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
	private User $user;

	protected function setUp(): void
	{
		$userData = [
			'UID' => 1,
			'username' => 'testuser',
			'password' => 'hashedpassword',
			'locale' => 'en_US',
			'company_id' => 123,
			'status' => 'active'
		];
		$roles = ['ROLE_USER', 'ROLE_ADMIN'];
		$this->user = new User($userData, $roles);
	}

	#[Group('units')]
	public function testGetPassword()
	{
		$this->assertEquals('hashedpassword', $this->user->getPassword());
	}

	#[Group('units')]
	public function testGetUsername()
	{
		$this->assertEquals('testuser', $this->user->getUsername());
	}

	#[Group('units')]
	public function testGetUID()
	{
		$this->assertEquals(1, $this->user->getUID());
	}

	#[Group('units')]
	public function testGetRoles()
	{
		$this->assertEquals(['ROLE_USER', 'ROLE_ADMIN'], $this->user->getRoles());
	}

	#[Group('units')]
	public function testGetLocale()
	{
		$this->assertEquals('en_US', $this->user->getLocale());
	}

	#[Group('units')]
	public function testGetCompanyId()
	{
		$this->assertEquals(123, $this->user->getCompanyId());
	}

	#[Group('units')]
	public function testGetStatus()
	{
		$this->assertEquals('active', $this->user->getStatus());
	}
}