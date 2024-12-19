<?php

namespace Tests\Unit\Framework\Utils\Html;

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
		$textField = new TextField(['id' => 'password']);

		$textField->setValue('secret123');

		$this->assertSame('secret123', $textField->getValue());
	}

	#[Group('units')]
	public function testGetValueDefault(): void
	{
		$textField = new TextField(['id' => 'email', 'default_value' => 'guest']);

		$this->assertSame('guest', $textField->getValue());
	}

	#[Group('units')]
	public function testSetValidationRules(): void
	{
		$textField = new TextField(['id' => 'email']);

		$textField->setValidationRules(['required' => true, 'email' => true]);

		$this->assertSame(['required' => true, 'email' => true], $textField->getValidationRules());
	}

	#[Group('units')]
	public function testSetAttribute(): void
	{
		$textField = new TextField(['id' => 'phone']);

		$textField->setAttribute('class', 'phone-input');

		$this->assertSame(['class' => 'phone-input'], $textField->getAttributes());
	}

	#[Group('units')]
	public function testAddValidationRule(): void
	{
		$textField = new TextField(['id' => 'website']);

		$textField->addValidationRule('url', true);

		$this->assertSame(['url' => true], $textField->getValidationRules());
	}
}
