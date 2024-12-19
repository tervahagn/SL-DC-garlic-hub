<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Utils\Html\CsrfTokenField;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class CsrfTokenFieldTest extends TestCase
{
	#[Group('units')]
	public function testSetupWithAttributes(): void
	{
		$attributes = [
			'id' => 'csrf_token',
			'name' => 'csrf_token_name'
		];

		$csrfTokenField = new \App\Framework\Utils\Html\CsrfTokenField($attributes);

		$this->assertSame('csrf_token', $csrfTokenField->getId());
		$this->assertSame('csrf_token_name', $csrfTokenField->getName());
		$this->assertNotEmpty($csrfTokenField->getValue());
		$this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $csrfTokenField->getValue());
	}

	#[Group('units')]
	public function testTokenIsGeneratedOnEachInstance(): void
	{
		$csrfTokenField1 = new \App\Framework\Utils\Html\CsrfTokenField(['id' => 'csrf1']);
		$csrfTokenField2 = new \App\Framework\Utils\Html\CsrfTokenField(['id' => 'csrf2']);

		$this->assertNotSame($csrfTokenField1->getValue(), $csrfTokenField2->getValue());
	}

	#[Group('units')]
	public function testTokenHasCorrectLength(): void
	{
		$csrfTokenField = new \App\Framework\Utils\Html\CsrfTokenField(['id' => 'csrf']);

		$this->assertSame(64, strlen($csrfTokenField->getValue()));
	}
}
