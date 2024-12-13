<?php

namespace Tests\Unit\Framework\OAuth2;

use App\Framework\OAuth2\ScopeEntity;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ScopeEntityTest extends TestCase
{
	private ScopeEntity $scopeEntity;

	/**
	 * setUp() wird vor jedem Test aufgerufen
	 */
	protected function setUp(): void
	{
		$this->scopeEntity = new ScopeEntity();
	}

	#[Group('units')]
	public function testImplementsScopeEntityInterface(): void
	{
		$this->assertInstanceOf(ScopeEntityInterface::class, $this->scopeEntity);
	}

	#[Group('units')]
	public function testGetIdentifierReturnsString(): void
	{
		$result = $this->scopeEntity->getIdentifier();
		$this->assertIsString($result);
	}

	#[Group('units')]
	public function testGetIdentifierReturnsEmptyString(): void
	{
		$result = $this->scopeEntity->getIdentifier();
		$this->assertSame('', $result);
	}

	#[Group('units')]
	public function testJsonSerializeReturnsSerializedData(): void
	{
		$result = $this->scopeEntity->jsonSerialize();
		$this->assertIsString($result);
		$this->assertSame('[]', $result);
	}
}
