<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Exceptions\FrameworkException;
use App\Framework\Utils\Html\CsrfTokenField;
use App\Framework\Utils\Html\EmailField;
use App\Framework\Utils\Html\FieldInterface;
use App\Framework\Utils\Html\FieldsFactory;
use App\Framework\Utils\Html\FieldsRenderFactory;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use App\Framework\Utils\Html\PasswordField;
use App\Framework\Utils\Html\TextField;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class FormBuilderTest extends TestCase
{
	private FieldsFactory       $fieldsFactoryMock;
	private FieldsRenderFactory $fieldsRenderFactoryMock;
	private FormBuilder         $formBuilder;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->fieldsFactoryMock = $this->createMock(FieldsFactory::class);
		$this->fieldsRenderFactoryMock = $this->createMock(FieldsRenderFactory::class);

		$this->formBuilder = new FormBuilder(
			$this->fieldsFactoryMock,
			$this->fieldsRenderFactoryMock
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
