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

use App\Framework\Exceptions\FrameworkException;
use App\Framework\OAuth2\ClientsRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClientsRepositoryTest extends TestCase
{
	private ClientsRepository&MockObject $repository;

	/**
	 * Wird vor jedem Test ausgeführt.
	 *
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$connectionMock = $this->createMock(Connection::class);

		$this->repository = $this->getMockBuilder(ClientsRepository::class)
								 ->setConstructorArgs([$connectionMock])
								 ->onlyMethods(['getFirstDataSet', 'findAllBy'])
								 ->getMock();
	}

	/**
	 * @throws FrameworkException
	 * @throws Exception
	 */
	#[Group('units')]
	public function testGetClientEntityReturnsClientEntity(): void
	{
		$clientData = [
			'client_id' => 'test-client-id',
			'redirect_uri' => 'https://example.com/callback',
			'client_name' => 'Test Client'
		];

		$this->repository->method('findAllBy')->willReturn([$clientData]);
		$this->repository->method('getFirstDataSet')->willReturn($clientData);

		$clientEntity = $this->repository->getClientEntity('test-client-id');

		$this->assertInstanceOf(ClientEntityInterface::class, $clientEntity);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testGetClientEntityReturnsNullIfClientNotFound(): void
	{
		$this->repository->method('findAllBy')->willReturn([]);
		$this->repository->method('getFirstDataSet')->willReturn(null);

		$this->expectException(FrameworkException::class);
		$this->expectExceptionMessage('Client not found');

		$this->repository->getClientEntity('non-existent-client-id');

	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testValidateClientReturnsTrueWhenValid(): void
	{
		$hashedSecret = password_hash('client-secret', PASSWORD_BCRYPT);
		$clientData = [
			'client_id' => 'test-client-id',
			'client_secret' => $hashedSecret,
			'grant_type' => 'authorization_code'
		];

		$this->repository->method('findAllBy')->willReturn([$clientData]);
		$this->repository->method('getFirstDataSet')->willReturn($clientData);

		$isValid = $this->repository->validateClient('test-client-id', 'client-secret');

		$this->assertTrue($isValid);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testValidateClientReturnsFalseWhenClientNotFound(): void
	{
		$this->repository->method('findAllBy')->willReturn([]);
		$this->repository->method('getFirstDataSet')->willReturn(null);

		$isValid = $this->repository->validateClient('non-existent-client-id', 'client-secret');

		$this->assertFalse($isValid);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testValidateClientReturnsFalseWhenSecretDoesNotMatch(): void
	{
		$hashedSecret = password_hash('correct-secret', PASSWORD_BCRYPT);
		$clientData = [
			'client_id' => 'test-client-id',
			'client_secret' => $hashedSecret,
			'grant_type' => 'authorization_code'
		];

		$this->repository->method('findAllBy')->willReturn([$clientData]);
		$this->repository->method('getFirstDataSet')->willReturn($clientData);

		$isValid = $this->repository->validateClient('test-client-id', 'wrong-secret');

		$this->assertFalse($isValid);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testValidateClientReturnsFalseWhenGrantTypeDoesNotMatch(): void
	{
		$hashedSecret = password_hash('client-secret', PASSWORD_BCRYPT);
		$clientData = [
			'client_id' => 'test-client-id',
			'client_secret' => $hashedSecret,
			'grant_type' => 'client_credentials'
		];

		$this->repository->method('findAllBy')->willReturn([$clientData]);
		$this->repository->method('getFirstDataSet')->willReturn($clientData);

		$isValid = $this->repository->validateClient('test-client-id', 'client-secret');

		$this->assertFalse($isValid);
	}
}
