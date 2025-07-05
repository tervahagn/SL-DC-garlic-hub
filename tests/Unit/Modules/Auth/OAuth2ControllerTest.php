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

namespace Tests\Unit\Modules\Auth;

use App\Framework\Core\Session;
use App\Modules\Auth\AuthService;
use App\Modules\Auth\OAuth2Controller;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class OAuth2ControllerTest extends TestCase
{
	private OAuth2Controller $controller;
	private AuthService&MockObject $authServiceMock;
	private AuthorizationServer&MockObject $authServerMock;
	private ServerRequestInterface&MockObject $requestMock;
	private ResponseInterface&MockObject $responseMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->authServiceMock = $this->createMock(AuthService::class);
		$this->authServerMock  = $this->createMock(AuthorizationServer::class);
		$this->requestMock     = $this->createMock(ServerRequestInterface::class);
		$this->responseMock    = $this->createMock(ResponseInterface::class);

		$this->controller = new OAuth2Controller($this->authServiceMock, $this->authServerMock);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testAuthorizeRedirectsToLoginIfUserNotLoggedIn(): void
	{
		$mockAuthRequest = $this->createMock(AuthorizationRequestInterface::class);
		$this->authServerMock->expects($this->once())->method('validateAuthorizationRequest')->willReturn($mockAuthRequest);

		$mockSession = $this->createMock(Session::class);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($mockSession);
		$this->requestMock->method('getQueryParams')
						  ->willReturn(['response_type' => 'code', 'client_id' => '123', 'redirect_uri' => 'https://example.com', 'state' => '123']);

		$mockSession->method('exists')->with('user')->willReturn(false);
		$mockSession->expects($this->once())->method('set');

		$this->responseMock->method('withHeader')->with('Location', '/login')->willReturnSelf();
		$this->responseMock->method('withStatus')->with(302)->willReturnSelf();

		$this->controller->authorize($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testAuthorizeOAuthServerException(): void
	{
		$this->authServerMock->expects($this->once())->method('validateAuthorizationRequest')
			->willThrowException(new OAuthServerException('error dam dam', 123, 'error'));

		$this->responseMock->expects($this->once())->method('withStatus')->with(400);
		$this->responseMock->expects($this->once())->method('withHeader')->willReturnSelf();

		$this->controller->authorize($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testAuthorizeException(): void
	{
		$this->authServerMock->expects($this->once())->method('validateAuthorizationRequest')
							 ->willThrowException(new \Exception('unknown bum bum'));


		$mockStreamInterface = $this->createMock(StreamInterface::class);
		$this->responseMock->expects($this->once())->method('getBody')->willReturn($mockStreamInterface);
		$mockStreamInterface->expects($this->once())->method('write');
		$this->responseMock->expects($this->once())->method('withStatus')->with(500);

		$this->controller->authorize($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testAuthorizeCompletesAuthorizationRequest(): void
	{
		$mockAuthRequest = $this->createMock(AuthorizationRequestInterface::class);
		$this->authServerMock->expects($this->once())->method('validateAuthorizationRequest')->willReturn($mockAuthRequest);

		$mockSession = $this->createMock(Session::class);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($mockSession);
		$this->requestMock->method('getQueryParams')
						  ->willReturn(['response_type' => 'code', 'client_id' => '123', 'redirect_uri' => 'https://example.com', 'state' => '123']);

		$mockSession->method('exists')->with('user')->willReturn(true);
		$mockSession->expects($this->never())->method('set');
		$mockSession->expects($this->once())->method('get')->with('user')
		            ->willReturn(['UID' => 159]);

		$this->authServiceMock->expects($this->once())->method('getCurrentUser')->with(159);
		$mockAuthRequest->expects($this->once())->method('setUser');
		$mockAuthRequest->expects($this->once())->method('setAuthorizationApproved')->with(true);

		$this->controller->authorize($this->requestMock, $this->responseMock);
	}

	#[Group('units')]
	public function testTokenReturnsAccessTokenResponse(): void
	{
		$this->authServerMock->method('respondToAccessTokenRequest')->willReturn($this->responseMock);

		$response = $this->controller->token($this->requestMock, $this->responseMock);

		// @phpstan-ignore-next-line
		static::assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testTokenOAuthServerException(): void
	{
		$this->authServerMock->expects($this->once())->method('respondToAccessTokenRequest')
							 ->willThrowException(new OAuthServerException('bäm', 123, 'error'));


		$this->responseMock->expects($this->once())->method('withStatus')->with(400);
		$this->responseMock->expects($this->once())->method('withHeader')->willReturnSelf();

		$this->controller->token($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testTokenException(): void
	{
		$this->authServerMock->expects($this->once())->method('respondToAccessTokenRequest')
							 ->willThrowException(new \Exception('unknown double bäm'));


		$mockStreamInterface = $this->createMock(StreamInterface::class);
		$this->responseMock->expects($this->once())->method('getBody')->willReturn($mockStreamInterface);
		$mockStreamInterface->expects($this->once())->method('write');
		$this->responseMock->expects($this->once())->method('withStatus')->with(500);

		$this->controller->token($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testValidationResponseTypeException(): void
	{
		$mockAuthRequest = $this->createMock(AuthorizationRequestInterface::class);
		$this->authServerMock->expects($this->once())->method('validateAuthorizationRequest')->willReturn($mockAuthRequest);

		$mockSession = $this->createMock(Session::class);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($mockSession);
		$params = ['response_type' => 'unknown', 'client_id' => '123', 'redirect_uri' => 'https://example.com', 'state' => '123'];

		$this->requestMock->method('getQueryParams')->willReturn($params);

		$this->controller->authorize($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testValidationEmptyClientIdException(): void
	{
		$mockAuthRequest = $this->createMock(AuthorizationRequestInterface::class);
		$this->authServerMock->expects($this->once())->method('validateAuthorizationRequest')->willReturn($mockAuthRequest);

		$mockSession = $this->createMock(Session::class);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($mockSession);
		$params = ['response_type' => 'code', 'client_id' => '', 'redirect_uri' => 'https://example.com', 'state' => '123'];

		$this->requestMock->method('getQueryParams')->willReturn($params);

		$this->controller->authorize($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testValidationTooLongClientIdException(): void
	{
		$mockAuthRequest = $this->createMock(AuthorizationRequestInterface::class);
		$this->authServerMock->expects($this->once())->method('validateAuthorizationRequest')->willReturn($mockAuthRequest);

		$mockSession = $this->createMock(Session::class);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($mockSession);
		$params = ['response_type' => 'code',
				   'client_id' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJlZGdlLWRlZmF1bHQtY2xpZW50IiwianRpIjoiODFkMDc5ODk2YWExMDNjMjgyYWZlZTNmYzYwYzI5Mzc3NTY0NDlmZjlkNzljMDA3OWZlMGQzMzVjNWM1ODZhMTNiNDBhNWVmM2EwNWZjZTIiLCJpYXQiOjE3MzQxMDkzOTIuOTc5NTAyLCJuYmYiOjE3MzQxMDkzOTIuOTc5NTA0LCJleHAiOjE3MzQxMTI5OTIuOTI5MDYzLCJzdWIiOiIiLCJzY29wZXMiOlsiW10iXX0.ZM0Cyilnq_vXcn3S5U9mpXqUVLbVwfUtVaxfvfju7bpdV3bajclm3euPZ-K6NppgxmFdA92UzUMCGNlBxeDI914Lsn3jB1IoW3mIDga7vzLBohvNbPIFi5W-zbHG9455KqhhpI-LY9O0wDf0VIhWk0XpGg3_m8xLUst-T1DnAkw4gIhorLZZMMiTNM5SyukjZ3-GrckbWD9-pCZxKnN5rznR_ixiNbkv_rBXEKIWdyeuHmgMlRsGJe7EeZInh7G2K_Dva_A0-D7gNFoLF6g_aKwK8YQVXSHdDwC8aIklO8gyfNMn_teIBxcbOTgifftcmnIov36phCetokiJG9YO8A',
				   'redirect_uri' => 'https://example.com', 'state' => '123'];

		$this->requestMock->method('getQueryParams')->willReturn($params);

		$this->controller->authorize($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testValidationRedirectUriException(): void
	{
		$mockAuthRequest = $this->createMock(AuthorizationRequestInterface::class);
		$this->authServerMock->expects($this->once())->method('validateAuthorizationRequest')->willReturn($mockAuthRequest);

		$mockSession = $this->createMock(Session::class);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($mockSession);
		$params = ['response_type' => 'code', 'client_id' => '122', 'redirect_uri' => 'http//example.com', 'state' => '123'];

		$this->requestMock->method('getQueryParams')->willReturn($params);

		$this->controller->authorize($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testValidationEmptyStateException(): void
	{
		$mockAuthRequest = $this->createMock(AuthorizationRequestInterface::class);
		$this->authServerMock->expects($this->once())->method('validateAuthorizationRequest')->willReturn($mockAuthRequest);

		$mockSession = $this->createMock(Session::class);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($mockSession);
		$params = ['response_type' => 'code', 'client_id' => '122', 'redirect_uri' => 'https://example.com', 'state' => ''];

		$this->requestMock->method('getQueryParams')->willReturn($params);

		$this->controller->authorize($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testValidationTooLongStateException(): void
	{
		$mockAuthRequest = $this->createMock(AuthorizationRequestInterface::class);
		$this->authServerMock->expects($this->once())->method('validateAuthorizationRequest')->willReturn($mockAuthRequest);

		$mockSession = $this->createMock(Session::class);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($mockSession);
		$params = ['response_type' => 'code', 'client_id' => '122', 'redirect_uri' => 'https://example.com',
				   'state' => 'def502007ebd82a5988d4a72e9feb405d85f1d9b911ce7172f195c42224bcaf2d981fecbe7a8902e04842221991fa0dff7b1ace944daa178a4ef09e42c5168653608cf71d54345645c3e3df4cf0527130b04df288ffed608f38aeadd338d0cd596901760fbb6ed1286f8079eac5a0c1042b7bb0155ec98122c1a1a2314f33e6f2b16c661c90676d4b63c774e75ce4478efa85cdadf16919b9ab17f662c5ed751320a953f980db8644563877a0d59e41701a01f2d86035ab68fa32562acabcc5a688c3da1bed6a694629bd6ca8ca632a7dd09b958eb84dffe557be32a42b1e0f7b270fa70178fdd2f65d3e4a0bae217766f29515a60f24c2b2ea040c0b8dbdf45457fbf68c60d5cbe5f3fa0b308cf258e569395a866c639aba266f50b657800555197c602b0066af6e0baaa42976357f13ff5bdf2b16fe16e9172a783f0623576ad6671a60e05c99daece5e607c0c76c3a6d4bab351ec5b0f8eceec64aa474b3475476e1d9f6dc4ba7cbbbaffd948c1578c5c465026bb75d6'
		];

		$this->requestMock->method('getQueryParams')->willReturn($params);

		$this->controller->authorize($this->requestMock, $this->responseMock);
	}
}
