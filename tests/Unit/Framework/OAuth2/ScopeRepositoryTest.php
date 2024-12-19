<?php

namespace Tests\Unit\Framework\OAuth2;

use App\Framework\OAuth2\ScopeRepository;
use Doctrine\DBAL\Connection;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ScopeRepositoryTest extends TestCase
{
	private ScopeRepository $repository;

	/**
	 * setUp() wird vor jedem Test aufgerufen
	 *
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$connectionMock = $this->createMock(Connection::class);
		$this->repository = new ScopeRepository($connectionMock);
	}

	#[Group('units')]
	public function testImplementsScopeRepositoryInterface(): void
	{
		$this->assertInstanceOf(ScopeRepositoryInterface::class, $this->repository);
	}

	#[Group('units')]
	public function testGetScopeEntityByIdentifierReturnsNull(): void
	{
		$result = $this->repository->getScopeEntityByIdentifier('non-existent-scope');
		$this->assertNull($result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFinalizeScopesReturnsEmptyArray(): void
	{
		$mockClientEntity = $this->createMock(ClientEntityInterface::class);

		$result = $this->repository->finalizeScopes([], 'authorization_code', $mockClientEntity);

		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}
}
