<?php

namespace Tests\Framework\Middleware;

use App\Framework\Middleware\SessionMiddleware;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Flash\Messages;
use SlimSession\Helper;

class SessionMiddlewareTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testProcessAddsSessionAndFlashAttributesToRequest(): void
	{
		session_start(); // Todo-> Put this shit to integration tests or find a way to not new Messages
		$sessionMock = $this->createMock(Helper::class);
		$requestMock = $this->createMock(ServerRequestInterface::class);
		$responseMock = $this->createMock(ResponseInterface::class);
		$handlerMock = $this->createMock(RequestHandlerInterface::class);

		// Mock the `withAttribute` method to ensure attributes are added
		$requestMock->expects($this->exactly(2))
					->method('withAttribute')
		         	->willReturnCallback(fn($key, $value) => $requestMock);


		// Ensure the handler's `handle` method is called with the modified request
		$handlerMock->expects($this->once())
					->method('handle')
					->with($requestMock)
					->willReturn($responseMock);

		$middleware = new SessionMiddleware($sessionMock);
		$result = $middleware->process($requestMock, $handlerMock);

		// Assert that the response from the handler is returned unchanged
		$this->assertSame($responseMock, $result);

		session_destroy();
	}
}
