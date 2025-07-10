<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);

namespace Tests\Unit\Framework\OAuth2;

use App\Framework\OAuth2\ClientEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ClientEntityTest extends TestCase
{
	#[Group('units')]
	public function testImplementsClientEntityInterface(): void
	{
		$client = [
			'client_id' => 'test-client-id',
			'redirect_uri' => 'https://example.com/callback',
			'client_name' => 'Test Client'
		];

		$clientEntity = new ClientEntity($client);
		// @phpstan-ignore-next-line
		static::assertInstanceOf(ClientEntityInterface::class, $clientEntity);
	}

	#[Group('units')]
	public function testConstructorInitializesPropertiesCorrectly(): void
	{
		$client = [
			'client_id' => 'test-client-id',
			'redirect_uri' => 'https://example.com/callback',
			'client_name' => 'Test Client'
		];

		$clientEntity = new ClientEntity($client);

		static::assertSame('test-client-id', $clientEntity->getIdentifier());
		static::assertSame('https://example.com/callback', $clientEntity->getRedirectUri());
		static::assertSame('Test Client', $clientEntity->getName());
		static::assertTrue($clientEntity->isConfidential());
	}

	#[Group('units')]
	public function testDefaultIsConfidentialIsTrue(): void
	{
		$client = [
			'client_id' => 'test-client-id',
			'redirect_uri' => 'https://example.com/callback',
			'client_name' => 'Test Client'
		];

		$clientEntity = new ClientEntity($client);
		static::assertTrue($clientEntity->isConfidential());
	}

	#[Group('units')]
	public function testFails(): void
	{
		$client = [
			'redirect_uri' => 'https://example.com/callback',
			'client_name' => 'Test Client'
		];
		static::expectException(\InvalidArgumentException::class);
		static::expectExceptionMessage('Client ID cannot be an empty.');

		$clientEntity = new ClientEntity($client);
		static::assertTrue($clientEntity->isConfidential());
	}

}
