<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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


namespace Tests\Unit\Framework\Core;

use App\Framework\Core\BaseValidator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class BaseValidatorTest extends TestCase
{
	private BaseValidator $baseValidator;

	protected function setUp(): void
	{
		parent::setUp();
		$this->baseValidator = new BaseValidator();
	}

	#[Group('units')]
	public function testIsEmailValid(): void
	{
		$this->assertTrue($this->baseValidator->isEmail('test@example.com'));
	}

	#[Group('units')]
	public function testIsEmailInvalid(): void
	{
		$this->assertFalse($this->baseValidator->isEmail('invalid-email'));
	}

	#[Group('units')]
	public function testIsJsonValid(): void
	{
		$this->assertTrue($this->baseValidator->isJson('{"key": "value"}'));
	}

	#[Group('units')]
	public function testIsJsonInvalid(): void
	{
		$this->assertFalse($this->baseValidator->isJson('invalid-json'));
	}

	#[Group('units')]
	public function testIsJsonEmpty(): void
	{
		$this->assertFalse($this->baseValidator->isJson(''));
	}
}
