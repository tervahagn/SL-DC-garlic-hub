<?php

namespace Tests\Unit\Framework\Middleware;

use App\Framework\Middleware\AuthMiddleware;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlimSession\Helper;

class AuthMiddlewareTest extends TestCase
{
	private AuthMiddleware $middleware;
	private ServerRequestInterface $requestMock;
	private RequestHandlerInterface $handlerMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->requestMock = $this->createMock(ServerRequestInterface::class);
		$this->handlerMock = $this->createMock(RequestHandlerInterface::class);
		$this->middleware = new AuthMiddleware();
	}


	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testPublicRouteAllowsAccess(): void
	{
		$this->requestMock->method('getUri')->willReturn($this->createConfiguredMock(UriInterface::class, ['getPath' => '/login']));

		$this->handlerMock->expects($this->once())->method('handle')->with($this->requestMock);

		$this->middleware->process($this->requestMock, $this->handlerMock);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testRedirectToLoginForUnauthorizedUser(): void
	{
		$this->requestMock->method('getUri')
			->willReturn($this->createConfiguredMock(UriInterface::class, [
				'getPath' => '/private'
			]));

		$this->requestMock->method('getAttribute')->with('session')
			->willReturn($this->createConfiguredMock(Helper::class, ['exists' => false]));

		$response = $this->middleware->process($this->requestMock, $this->handlerMock);

		$this->assertInstanceOf(ResponseInterface::class, $response);
		$this->assertEquals(302, $response->getStatusCode());
		$this->assertEquals(['/login'], $response->getHeader('Location'));
	}

	#[Group('units')]
	public function testAuthorizedUserProceeds(): void
	{
		$this->requestMock->method('getUri')
			->willReturn($this->createConfiguredMock(UriInterface::class, [
				'getPath' => '/private'
			]));

		$this->requestMock->method('getAttribute')->with('session')
			->willReturn($this->createConfiguredMock(Helper::class, [
				'exists' => true
			]));

		$this->handlerMock->expects($this->once())->method('handle')->with($this->requestMock);
		$this->middleware->process($this->requestMock, $this->handlerMock);
	}
}
