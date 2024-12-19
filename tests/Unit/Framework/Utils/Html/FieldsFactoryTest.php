<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Utils\Html\CsrfTokenField;
use App\Framework\Utils\Html\EmailField;
use App\Framework\Utils\Html\FieldsFactory;
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
		$attributes = ['id' => 'username', 'name' => 'user_name'];

		$field = $this->fieldsFactory->createTextField($attributes);

		$this->assertInstanceOf(TextField::class, $field);
		$this->assertSame('username', $field->getId());
		$this->assertSame('user_name', $field->getName());
	}

	#[Group('units')]
	public function testCreateEmailField(): void
	{
		$attributes = ['id' => 'email', 'name' => 'email_address'];

		$field = $this->fieldsFactory->createEmailField($attributes);

		$this->assertInstanceOf(EmailField::class, $field);
		$this->assertSame('email', $field->getId());
		$this->assertSame('email_address', $field->getName());
	}

	#[Group('units')]
	public function testCreatePasswordField(): void
	{
		$attributes = ['id' => 'password', 'name' => 'user_password'];

		$field = $this->fieldsFactory->createPasswordField($attributes);

		$this->assertInstanceOf(PasswordField::class, $field);
		$this->assertSame('password', $field->getId());
		$this->assertSame('user_password', $field->getName());
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCreateCsrfTokenField(): void
	{
		$attributes = ['id' => 'csrf_token', 'name' => 'csrf_token_name'];

		$field = $this->fieldsFactory->createCsrfTokenField($attributes);

		$this->assertInstanceOf(CsrfTokenField::class, $field);
		$this->assertSame('csrf_token', $field->getId());
		$this->assertSame('csrf_token_name', $field->getName());
		$this->assertNotEmpty($field->getValue());
	}
}
