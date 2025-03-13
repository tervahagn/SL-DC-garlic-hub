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

use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\TextField;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class TextFieldTest extends TestCase
{
	#[Group('units')]
	public function testSetupWithAttributes(): void
	{
		$attributes = [
			'id' => 'username',
			'type' => FieldType::TEXT,
			'name' => 'user_name',
			'value' => 'defaultUser',
			'default_value' => 'guest',
			'rules' => ['required' => true],
			'attributes' => ['class' => 'form-control', 'placeholder' => 'Enter username']
		];

		$textField = new TextField($attributes);

		$this->assertSame('username', $textField->getId());
		$this->assertSame('user_name', $textField->getName());
		$this->assertSame('defaultUser', $textField->getValue());
		$this->assertSame(['required' => true], $textField->getValidationRules());
		$this->assertSame(['class' => 'form-control', 'placeholder' => 'Enter username'], $textField->getAttributes());
	}

	#[Group('units')]
	public function testSetValue(): void
	{
		$textField = new TextField(['id' => 'password', 'type' => FieldType::TEXT]);

		$textField->setValue('secret123');

		$this->assertSame('secret123', $textField->getValue());
	}

	#[Group('units')]
	public function testGetValueDefault(): void
	{
		$textField = new TextField(['id' => 'email', 'type' => FieldType::TEXT, 'default_value' => 'guest']);

		$this->assertSame('guest', $textField->getValue());
	}

	#[Group('units')]
	public function testSetValidationRules(): void
	{
		$textField = new TextField(['id' => 'email', 'type' => FieldType::TEXT]);

		$textField->setValidationRules(['required' => true, 'email' => true]);

		$this->assertSame(['required' => true, 'email' => true], $textField->getValidationRules());
	}

	#[Group('units')]
	public function testSetAttribute(): void
	{
		$textField = new TextField(['id' => 'phone', 'type' => FieldType::TEXT]);

		$textField->setAttribute('class', 'phone-input');

		$this->assertSame(['class' => 'phone-input'], $textField->getAttributes());
	}

	#[Group('units')]
	public function testAddValidationRule(): void
	{
		$textField = new TextField(['id' => 'website', 'type' => FieldType::TEXT]);

		$textField->addValidationRule('url');

		$this->assertSame(['url' => true], $textField->getValidationRules());
	}
}
