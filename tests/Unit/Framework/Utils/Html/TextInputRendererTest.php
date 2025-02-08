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

use App\Framework\Utils\Html\FieldInterface;
use App\Framework\Utils\Html\TextRenderer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class TextInputRendererTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testRenderWithBasicAttributes(): void
	{
		$fieldMock = $this->createMock(FieldInterface::class);
		$fieldMock->method('getName')->willReturn('username');
		$fieldMock->method('getId')->willReturn('user_1');
		$fieldMock->method('getValue')->willReturn('JohnDoe');
		$fieldMock->method('getValidationRules')->willReturn([]);
		$fieldMock->method('getAttributes')->willReturn([]);

		$renderer = new TextRenderer();
		$result = $renderer->render($fieldMock);

		$expected = '<input type="text" name="username" id="user_1" value="JohnDoe" aria-describedby="error_user_1">';
		$this->assertSame($expected, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testRenderWithValidationRules(): void
	{
		$fieldMock = $this->createMock(FieldInterface::class);
		$fieldMock->method('getName')->willReturn('email');
		$fieldMock->method('getId')->willReturn('email_input');
		$fieldMock->method('getValue')->willReturn('john@example.com');
		$fieldMock->method('getValidationRules')->willReturn([
			'required' => true,
			'maxlength' => 255
		]);
		$fieldMock->method('getAttributes')->willReturn([]);

		$renderer = new TextRenderer();
		$result = $renderer->render($fieldMock);

		$expected = '<input type="text" name="email" id="email_input" value="john@example.com" required="required" maxlength="255" aria-describedby="error_email_input">';
		$this->assertSame($expected, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testRenderWithAdditionalAttributes(): void
	{
		$fieldMock = $this->createMock(FieldInterface::class);
		$fieldMock->method('getName')->willReturn('username');
		$fieldMock->method('getId')->willReturn('user_1');
		$fieldMock->method('getValue')->willReturn('JohnDoe');
		$fieldMock->method('getValidationRules')->willReturn([]);
		$fieldMock->method('getAttributes')->willReturn([
			'class' => 'form-control',
			'placeholder' => 'Enter your username'
		]);

		$renderer = new TextRenderer();
		$result = $renderer->render($fieldMock);

		$expected = '<input type="text" name="username" id="user_1" value="JohnDoe" class="form-control" placeholder="Enter your username" aria-describedby="error_user_1">';
		$this->assertSame($expected, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testRenderWithAllOptions(): void
	{
		$fieldMock = $this->createMock(FieldInterface::class);
		$fieldMock->method('getName')->willReturn('password');
		$fieldMock->method('getId')->willReturn('password_input');
		$fieldMock->method('getValue')->willReturn('');
		$fieldMock->method('getValidationRules')->willReturn([
			'required' => true,
			'maxlength' => 20
		]);
		$fieldMock->method('getAttributes')->willReturn([
			'class' => 'password-input',
			'placeholder' => 'Enter your password'
		]);

		$renderer = new TextRenderer();
		$result = $renderer->render($fieldMock);

		$expected = '<input type="text" name="password" id="password_input" value="" class="password-input" placeholder="Enter your password" required="required" maxlength="20" aria-describedby="error_password_input">';
		$this->assertSame($expected, $result);
	}
}
