<?php

namespace Tests\Unit\Framework\Middleware;

use App\Framework\Core\Config\Config;
use App\Framework\Core\Locales\Locales;
use App\Framework\Core\Translate\Translator;
use App\Framework\Middleware\EnvironmentMiddleware;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EnvironmentMiddlewareTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testProcessAddsAttributesAndCallsNextHandler(): void
	{
		$configMock     = $this->createMock(Config::class);
		$localesMock    = $this->createMock(Locales::class);
		$translatorMock = $this->createMock(Translator::class);

		$localesMock->expects($this->once())->method('determineCurrentLocale');

		$requestMock = $this->createMock(ServerRequestInterface::class);
		$requestMock->method('withAttribute')->willReturnSelf();

		$responseMock = $this->createMock(ResponseInterface::class);

		$handlerMock = $this->createMock(RequestHandlerInterface::class);
		$handlerMock->expects($this->once())
					->method('handle')
					->with($this->isInstanceOf(ServerRequestInterface::class))
					->willReturn($responseMock);

		$middleware = new EnvironmentMiddleware($configMock, $localesMock, $translatorMock);
		$result     = $middleware->process($requestMock, $handlerMock);

		$this->assertSame($responseMock, $result);
	}
}
