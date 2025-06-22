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
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Utils\Html\CsrfTokenField;
use App\Framework\Utils\Html\EmailField;
use App\Framework\Utils\Html\FieldInterface;
use App\Framework\Utils\Html\FieldsFactory;
use App\Framework\Utils\Html\FieldsRenderFactory;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use App\Framework\Utils\Html\HiddenField;
use App\Framework\Utils\Html\PasswordField;
use App\Framework\Utils\Html\TextField;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormBuilderTest extends TestCase
{
	private FieldsFactory&MockObject       $fieldsFactoryMock;
	private FieldsRenderFactory&MockObject $fieldsRenderFactoryMock;
	private FormBuilder         $formBuilder;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->fieldsFactoryMock       = $this->createMock(FieldsFactory::class);
		$this->fieldsRenderFactoryMock = $this->createMock(FieldsRenderFactory::class);
		$csrfTokenMock = $this->createMock(CsrfToken::class);
		$this->formBuilder             = new FormBuilder(
			$this->fieldsFactoryMock,
			$this->fieldsRenderFactoryMock,
			$csrfTokenMock
		);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testPrepareFormWithMixedFields(): void
	{
		$textFieldMock = $this->createMock(TextField::class);
		$textFieldMock->method('getType')->willReturn(FieldType::TEXT);
		$textFieldMock->method('getId')->willReturn('text_field_1');
		$textFieldMock->method('getLabel')->willReturn('Text Field');

		$csrfFieldMock = $this->createMock(CsrfTokenField::class);
		$csrfFieldMock->method('getType')->willReturn(FieldType::CSRF);

		$this->fieldsRenderFactoryMock
			->method('getRenderer')
			->willReturnMap([
				[$textFieldMock, '<input type="text" id="text_field_1" />'],
				[$csrfFieldMock, '<input type="hidden" name="csrf_token" />']
			]);

		$result = $this->formBuilder->prepareForm([$textFieldMock, $csrfFieldMock]);

		$this->assertCount(1, $result['visible']);
		$this->assertSame(
			[
				'HTML_ELEMENT_ID' => 'text_field_1',
				'LANG_ELEMENT_NAME' => 'Text Field',
				'ELEMENT_MUST_FIELD' => '',
				'HTML_ELEMENT' => '<input type="text" id="text_field_1" />',
			],
			$result['visible'][0]
		);

		$this->assertCount(1, $result['hidden']);
		$this->assertSame(
			[
				'HIDDEN_HTML_ELEMENT' => '<input type="hidden" name="csrf_token" />',
			],
			$result['hidden'][0]
		);
	}

	#[Group('units')]
	public function testPrepareFormWithEmptyFields(): void
	{
		$result = $this->formBuilder->prepareForm([]);

		$this->assertEmpty($result['visible']);
		$this->assertEmpty($result['hidden']);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testPrepareFormWithOnlyHiddenFields(): void
	{
		$hiddenFieldMock = $this->createMock(HiddenField::class);
		$hiddenFieldMock->method('getType')->willReturn(FieldType::HIDDEN);
		$this->fieldsRenderFactoryMock
			->method('getRenderer')
			->with($hiddenFieldMock)
			->willReturn('<input type="hidden" id="hidden_field_1" />');

		$result = $this->formBuilder->prepareForm([$hiddenFieldMock]);

		$this->assertEmpty($result['visible']);
		$this->assertCount(1, $result['hidden']);
		$this->assertSame(
			[
				'HIDDEN_HTML_ELEMENT' => '<input type="hidden" id="hidden_field_1" />',
			],
			$result['hidden'][0]
		);
	}

	#[Group('units')]
	public function testPrepareFormWithOnlyVisibleFields(): void
	{
		$visibleFieldMock = $this->createMock(TextField::class);
		$visibleFieldMock->method('getType')->willReturn(FieldType::TEXT);
		$visibleFieldMock->method('getId')->willReturn('visible_field_1');
		$visibleFieldMock->method('getLabel')->willReturn('Visible Field');
		$this->fieldsRenderFactoryMock
			->method('getRenderer')
			->with($visibleFieldMock)
			->willReturn('<input type="text" id="visible_field_1" />');

		$result = $this->formBuilder->prepareForm([$visibleFieldMock]);

		$this->assertEmpty($result['hidden']);
		$this->assertCount(1, $result['visible']);
		$this->assertSame(
			[
				'HTML_ELEMENT_ID' => 'visible_field_1',
				'LANG_ELEMENT_NAME' => 'Visible Field',
				'ELEMENT_MUST_FIELD' => '',
				'HTML_ELEMENT' => '<input type="text" id="visible_field_1" />',
			],
			$result['visible'][0]
		);
	}


	/**
	 * @throws Exception
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testCreateTextField(): void
	{
		$fieldMock = $this->createMock(TextField::class);

		$this->fieldsFactoryMock
			->expects($this->once())
			->method('createTextField')
			->with(['type' => FieldType::TEXT])
			->willReturn($fieldMock);

		$field = $this->formBuilder->createField(['type' => FieldType::TEXT]);

		$this->assertSame($fieldMock, $field);
	}

	/**
	 * @throws Exception
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testCreatePasswordField(): void
	{
		$fieldMock = $this->createMock(PasswordField::class);

		$this->fieldsFactoryMock
			->expects($this->once())
			->method('createPasswordField')
			->with(['type' => FieldType::PASSWORD])
			->willReturn($fieldMock);

		$field = $this->formBuilder->createField(['type' => FieldType::PASSWORD]);

		$this->assertSame($fieldMock, $field);
	}

	/**
	 * @throws Exception
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testCreateEmailField(): void
	{
		$fieldMock = $this->createMock(EmailField::class);

		$this->fieldsFactoryMock
			->expects($this->once())
			->method('createEmailField')
			->with(['type' => FieldType::EMAIL])
			->willReturn($fieldMock);

		$field = $this->formBuilder->createField(['type' => FieldType::EMAIL]);

		$this->assertSame($fieldMock, $field);
	}

	/**
	 * @throws Exception
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testCreateCsrfTokenField(): void
	{
		$fieldMock = $this->createMock(CsrfTokenField::class);

		$this->fieldsFactoryMock
			->expects($this->once())
			->method('createCsrfTokenField')
			->with(['type' => FieldType::CSRF])
			->willReturn($fieldMock);

		$field = $this->formBuilder->createField(['type' => FieldType::CSRF]);

		$this->assertSame($fieldMock, $field);
	}

	#[Group('units')]
	public function testInvalidFieldTypeThrowsException(): void
	{
		$this->expectException(FrameworkException::class);

		$this->formBuilder->createField(['type' => 'invalid']);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testRenderField(): void
	{
		$fieldMock = $this->createMock(FieldInterface::class);

		$this->fieldsRenderFactoryMock
			->expects($this->once())
			->method('getRenderer')
			->with($fieldMock)
			->willReturn('<input type="text" />');

		$output = $this->formBuilder->renderField($fieldMock);

		$this->assertSame('<input type="text" />', $output);
	}
}
