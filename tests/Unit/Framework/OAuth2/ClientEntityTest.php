<?php

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
		$this->assertInstanceOf(ClientEntityInterface::class, $clientEntity);
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

		$this->assertSame('test-client-id', $clientEntity->getIdentifier());
		$this->assertSame('https://example.com/callback', $clientEntity->getRedirectUri());
		$this->assertSame('Test Client', $clientEntity->getName());
		$this->assertTrue($clientEntity->isConfidential());
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
		$this->assertTrue($clientEntity->isConfidential());
	}
}
