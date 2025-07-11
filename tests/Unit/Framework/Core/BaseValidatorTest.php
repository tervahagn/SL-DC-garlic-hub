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
declare(strict_types=1);

namespace Tests\Unit\Framework\Core;

use App\Framework\Core\BaseValidator;
use App\Framework\Core\CsrfToken;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class BaseValidatorTest extends TestCase
{
	private BaseValidator $baseValidator;
	private CsrfToken&\PHPUnit\Framework\MockObject\MockObject $csrfTokenMock;

	protected function setUp(): void
	{
		parent::setUp();
		$this->csrfTokenMock = $this->createMock(CsrfToken::class);
		$this->baseValidator = new BaseValidator($this->csrfTokenMock);
	}

	#[Group('units')]
	public function testIsEmailValid(): void
	{
		static::assertTrue($this->baseValidator->isEmail('test@example.com'));
	}

	#[Group('units')]
	public function testIsEmailInvalid(): void
	{
		static::assertFalse($this->baseValidator->isEmail('invalid-email'));
	}

	#[Group('units')]
	public function testIsJsonValid(): void
	{
		static::assertTrue($this->baseValidator->isJson('{"key": "value"}'));
	}

	#[Group('units')]
	public function testIsJsonInvalid(): void
	{
		static::assertFalse($this->baseValidator->isJson('invalid-json'));
	}

	#[Group('units')]
	public function testIsJsonEmpty(): void
	{
		static::assertFalse($this->baseValidator->isJson(''));
	}
	#[Group('units')]
	public function testValidateCsrfTokenValid(): void
	{
		$token = 'validCsrfToken';

		$this->csrfTokenMock
			->expects($this->once())
			->method('validateToken')
			->with($token)
			->willReturn(true);

		static::assertTrue($this->baseValidator->validateCsrfToken($token));
	}

	#[Group('units')]
	public function testValidateCsrfTokenInvalid(): void
	{
		$token = 'invalidCsrfToken';

		$this->csrfTokenMock->expects($this->once())->method('validateToken')
			->with($token)
			->willReturn(false);

		static::assertFalse($this->baseValidator->validateCsrfToken($token));
	}
}
