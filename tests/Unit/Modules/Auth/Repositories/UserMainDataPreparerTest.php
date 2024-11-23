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

use App\Framework\Database\DBHandler;
use App\Modules\Auth\Repositories\UserMainDataPreparer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class UserMainDataPreparerTest extends TestCase
{
	private DBHandler $dbhMock;

	protected function setUp(): void
	{
		$this->dbhMock = $this->createMock(DBHandler::class);
	}


	#[Group('units')]
	public function testPrepareForDBQuotesAndFormatsFieldsCorrectly(): void
	{
		$dataPreparer = new UserMainDataPreparer($this->dbhMock);
		$fields = [
			'UID' => 1,
			'company_id' => 123,
			'lastaccess' => '2024-01-01 00:00:00',
			'logintime' => '2024-01-01 00:00:00',
			'since' => '2024-01-01 00:00:00',
			'2fa' => 'enabled',
			'status' => 1,
			'logged' => 1,
			'lastIP' => '192.168.1.1',
			'birthday' => '1990-01-01',
			'locale' => 'en_US',
			'SID' => 'session123',
			'username' => 'testuser',
			'password' => 'hashedpassword',
			'gender' => 'male',
			'email' => 'test@example.com'
		];

		$preparedFields = $dataPreparer->prepareForDB($fields);

		$this->assertIsArray($preparedFields);
		$this->assertArrayHasKey('UID', $preparedFields);
		$this->assertArrayHasKey('company_id', $preparedFields);
		$this->assertArrayHasKey('lastaccess', $preparedFields);
		$this->assertArrayHasKey('logintime', $preparedFields);
		$this->assertArrayHasKey('since', $preparedFields);
		$this->assertArrayHasKey('2fa', $preparedFields);
		$this->assertArrayHasKey('status', $preparedFields);
		$this->assertArrayHasKey('logged', $preparedFields);
		$this->assertArrayHasKey('lastIP', $preparedFields);
		$this->assertArrayHasKey('birthday', $preparedFields);
		$this->assertArrayHasKey('locale', $preparedFields);
		$this->assertArrayHasKey('SID', $preparedFields);
		$this->assertArrayHasKey('username', $preparedFields);
		$this->assertArrayHasKey('password', $preparedFields);
		$this->assertArrayHasKey('gender', $preparedFields);
		$this->assertArrayHasKey('email', $preparedFields);
	}

	#[Group('units')]
	public function testPrepareForDBHandlesEmptyFieldsArray(): void
	{
		$dataPreparer   = new UserMainDataPreparer($this->dbhMock);
		$fields         = [];
		$preparedFields = $dataPreparer->prepareForDB($fields);

		$this->assertIsArray($preparedFields);
		$this->assertEmpty($preparedFields);
	}

	#[Group('units')]
	public function testPrepareForDBHandlesMissingFields(): void
	{
		$dataPreparer = new UserMainDataPreparer($this->dbhMock);
		$fields = [
			'UID' => 1,
			'username' => 'testuser'
		];

		$preparedFields = $dataPreparer->prepareForDB($fields);

		$this->assertIsArray($preparedFields);
		$this->assertArrayHasKey('UID', $preparedFields);
		$this->assertArrayHasKey('username', $preparedFields);
		$this->assertArrayNotHasKey('company_id', $preparedFields);
	}
}
