<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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


namespace Tests\Unit\Framework\Core;

use App\Framework\Core\Session;
use App\Framework\Exceptions\FrameworkException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
	private Session $session;

	protected function setUp(): void
	{
		session_unset();
		session_destroy();
		$this->session = new Session(); // Or new Session('TestSession') if you want a custom name
		$_SESSION = [];
	}

	#[Group('units')]
	public function testStart()
	{
		$this->session->start();
		$this->assertEquals(PHP_SESSION_ACTIVE, session_status());
	}

	/**
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testRegenerateID()
	{
		$this->session->start();
		$oldId = session_id();
		$this->session->regenerateID();
		$newId = session_id();

		$this->assertNotEquals($oldId, $newId);
	}

	#[Group('units')]
	public function testRegenerateIDSessionNotActive()
	{
		$this->expectException(FrameworkException::class);
		$this->expectExceptionMessage('Session not active for regenerating.');
		$this->session->regenerateID();
	}

	#[Group('units')]
	public function testGet()
	{
		$_SESSION['test_key'] = 'test_value';
		$this->assertEquals('test_value', $this->session->get('test_key'));
		$this->assertNull($this->session->get('non_existent_key'));
	}

	#[Group('units')]
	public function testSet()
	{
		$this->session->set('test_key', 'test_value');
		$this->assertEquals('test_value', $_SESSION['test_key']);

		$testArray = ['a' => 1, 'b' => 2];
		$this->session->set('test_array', $testArray);
		$this->assertEquals($testArray, $_SESSION['test_array']);
	}

	#[Group('units')]
	public function testDelete()
	{
		$_SESSION['test_key'] = 'test_value';
		$this->session->delete('test_key');
		$this->assertFalse(array_key_exists('test_key', $_SESSION));

		$this->session->delete('non_existent_key'); // Should not cause an error
	}

	#[Group('units')]
	public function testClear()
	{
		$_SESSION['test_key'] = 'test_value';
		$_SESSION['another_key'] = 'another_value';
		$this->session->clear();
		$this->assertEmpty($_SESSION);
	}

	#[Group('units')]
	public function testExists()
	{
		$_SESSION['test_key'] = 'test_value';
		$this->assertTrue($this->session->exists('test_key'));
		$this->assertFalse($this->session->exists('non_existent_key'));
	}

	#[Group('units')]
	public function testId()
	{
		$this->session->start();
		$id = Session::id();
		$this->assertNotEmpty($id);

		$newId = Session::id(true); //regenerate
		$this->assertNotEmpty($newId);
		$this->assertNotEquals($id, $newId);
	}

	#[Group('units')]
	public function testIdNotStarted()
	{
		$id = Session::id();
		$this->assertEmpty($id); // Should be empty if the session hasn't started
	}
}
