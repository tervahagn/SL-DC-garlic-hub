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

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Core\CsrfToken;
use App\Framework\Core\Session;
use App\Framework\Utils\Html\CsrfTokenField;
use App\Framework\Utils\Html\FieldType;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CsrfTokenFieldTest extends TestCase
{

	private CsrfToken&MockObject $csrfTokeMock;

	/**
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$this->csrfTokeMock = $this->createMock(CsrfToken::class);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testSetupWithAttributes(): void
	{
		$attributes = [
			'id' => 'csrf_token',
			'type' => FieldType::CSRF,
			'name' => 'csrf_token_name'
		];

		$token = 'token';
		$this->csrfTokeMock->expects($this->once())->method('getToken')
			->willReturn($token);

		$csrfTokenField = new CsrfTokenField($attributes, $this->csrfTokeMock);

		$this->assertSame('csrf_token', $csrfTokenField->getId());
		$this->assertSame('csrf_token_name', $csrfTokenField->getName());
		$this->assertNotEmpty($csrfTokenField->getValue());
	}

}
