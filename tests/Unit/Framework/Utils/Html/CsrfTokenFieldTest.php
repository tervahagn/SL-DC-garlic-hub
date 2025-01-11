<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Core\Session;
use App\Framework\Utils\Html\CsrfTokenField;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class CsrfTokenFieldTest extends TestCase
{

	private Session $sessionMock;

	protected function setUp(): void
	{
		$this->sessionMock = $this->createMock(Session::class);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testSetupWithAttributes(): void
	{
		$attributes = [
			'id' => 'csrf_token',
			'name' => 'csrf_token_name'
		];

		$this->sessionMock->expects($this->once())->method('set')
			->with('csrf_token', $this->callback(function ($token)
			{
				return (is_string($token) && strlen($token) === 64);
			})
		);

		$csrfTokenField = new CsrfTokenField($attributes, $this->sessionMock);

		$this->assertSame('csrf_token', $csrfTokenField->getId());
		$this->assertSame('csrf_token_name', $csrfTokenField->getName());
		$this->assertNotEmpty($csrfTokenField->getValue());
		$this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $csrfTokenField->getValue());
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testTokenIsGeneratedOnEachInstance(): void
	{
		$csrfTokenField1 = new CsrfTokenField(['id' => 'csrf1'], $this->sessionMock);
		$csrfTokenField2 = new CsrfTokenField(['id' => 'csrf2'], $this->sessionMock);

		$this->assertNotSame($csrfTokenField1->getValue(), $csrfTokenField2->getValue());
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testTokenHasCorrectLength(): void
	{
		$csrfTokenField = new CsrfTokenField(['id' => 'csrf'], $this->sessionMock);

		$this->assertSame(64, strlen($csrfTokenField->getValue()));
	}
}
