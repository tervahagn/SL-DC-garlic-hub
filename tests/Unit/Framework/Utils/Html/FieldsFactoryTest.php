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

use App\Framework\Core\Session;
use App\Framework\Utils\Html\AutocompleteField;
use App\Framework\Utils\Html\CsrfTokenField;
use App\Framework\Utils\Html\DropdownField;
use App\Framework\Utils\Html\EmailField;
use App\Framework\Utils\Html\FieldsFactory;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\HiddenField;
use App\Framework\Utils\Html\NumberField;
use App\Framework\Utils\Html\PasswordField;
use App\Framework\Utils\Html\TextField;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class FieldsFactoryTest extends TestCase
{
	private FieldsFactory $fieldsFactory;

	protected function setUp(): void
	{
		$this->fieldsFactory = new FieldsFactory();
	}

	#[Group('units')]
	public function testCreateTextField(): void
	{
		$attributes = ['id' => 'username', 'type' => FieldType::TEXT, 'name' => 'user_name'];

		$field = $this->fieldsFactory->createTextField($attributes);

		$this->assertInstanceOf(TextField::class, $field);
		$this->assertSame('username', $field->getId());
		$this->assertSame('user_name', $field->getName());
	}

	#[Group('units')]
	public function testCreateNumberField(): void
	{
		$attributes = ['id' => 'status', 'type' => FieldType::NUMBER, 'name' => 'user_status'];

		$field = $this->fieldsFactory->createNumberField($attributes);

		$this->assertInstanceOf(NumberField::class, $field);
		$this->assertSame('status', $field->getId());
		$this->assertSame('user_status', $field->getName());
	}

	#[Group('units')]
	public function testCreateAutocompleteField(): void
	{
		$attributes = ['id' => 'UID', 'type' => FieldType::AUTOCOMPLETE, 'name' => 'username', 'data-label' => 'test-label'];

		$field = $this->fieldsFactory->createAutocompleteField($attributes);

		$this->assertInstanceOf(AutocompleteField::class, $field);
		$this->assertSame('UID', $field->getId());
		$this->assertSame('username', $field->getName());
	}

	#[Group('units')]
	public function testCreateDropdownField(): void
	{
		$attributes = ['id' => 'countries', 'type' => FieldType::DROPDOWN, 'name' => 'countries_names', 'options' => []];

		$field = $this->fieldsFactory->createDropdownField($attributes);

		$this->assertInstanceOf(DropdownField::class, $field);
		$this->assertSame('countries', $field->getId());
		$this->assertSame('countries_names', $field->getName());
	}


	#[Group('units')]
	public function testCreateEmailField(): void
	{
		$attributes = ['id' => 'email', 'type' => FieldType::EMAIL, 'name' => 'email_address'];

		$field = $this->fieldsFactory->createEmailField($attributes);

		$this->assertInstanceOf(EmailField::class, $field);
		$this->assertSame('email', $field->getId());
		$this->assertSame('email_address', $field->getName());
	}

	#[Group('units')]
	public function testCreatePasswordField(): void
	{
		$attributes = ['id' => 'password', 'type' => FieldType::PASSWORD, 'name' => 'user_password'];

		$field = $this->fieldsFactory->createPasswordField($attributes);

		$this->assertInstanceOf(PasswordField::class, $field);
		$this->assertSame('password', $field->getId());
		$this->assertSame('user_password', $field->getName());
	}

	/**
	 * @throws Exception
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	#[Group('units')]
	public function testCreateCsrfTokenField(): void
	{
		$attributes = ['id' => 'csrf_token', 'type' => FieldType::CSRF, 'name' => 'csrf_token_name'];

		$field = $this->fieldsFactory->createCsrfTokenField($attributes, $this->createMock(Session::class));

		$this->assertInstanceOf(CsrfTokenField::class, $field);
		$this->assertSame('csrf_token', $field->getId());
		$this->assertSame('csrf_token_name', $field->getName());
		$this->assertNotEmpty($field->getValue());
	}

	#[Group('units')]
	public function testCreateHiddenField(): void
	{
		$attributes = ['id' => 'username', 'type' => FieldType::HIDDEN, 'name' => 'user_name'];

		$field = $this->fieldsFactory->createHiddenField($attributes);

		$this->assertInstanceOf(HiddenField::class, $field);
		$this->assertSame('username', $field->getId());
		$this->assertSame('user_name', $field->getName());
	}
}
