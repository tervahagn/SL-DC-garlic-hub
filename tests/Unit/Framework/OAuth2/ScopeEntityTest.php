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
		// @phpstan-ignore-next-line
		$this->assertInstanceOf(ScopeEntityInterface::class, $this->scopeEntity);
	}

	#[Group('units')]
	public function testGetIdentifierReturnsString(): void
	{
		$result = $this->scopeEntity->getIdentifier();
		$this->assertSame('dummy', $result);
	}

	#[Group('units')]
	public function testGetIdentifierReturnsEmptyString(): void
	{
		$result = $this->scopeEntity->getIdentifier();
		$this->assertSame('dummy', $result);
	}

	#[Group('units')]
	public function testJsonSerializeReturnsSerializedData(): void
	{
		$result = $this->scopeEntity->jsonSerialize();
		$this->assertIsString($result);
		$this->assertSame('[]', $result);
	}
}
