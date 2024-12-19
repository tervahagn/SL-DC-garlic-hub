<?php

namespace Tests\Unit\Framework\Middleware;

use App\Framework\Core\Config\Config;
use App\Framework\Core\Locales\Locales;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Middleware\LayoutDataMiddleware;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\InvalidArgumentException;
use SlimSession\Helper;

class LayoutDataMiddlewareTest extends TestCase
{
    private LayoutDataMiddleware $middleware;
    private Translator $translatorMock;
    private Helper $sessionMock;
	private Config $configMock;
    private ServerRequestInterface $requestMock;
    private RequestHandlerInterface $handlerMock;
    private ResponseInterface $responseMMock;
	private Locales $localesMockk;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->translatorMock = $this->createMock(Translator::class);
        $this->sessionMock    = $this->createMock(Helper::class);
        $this->requestMock    = $this->createMock(ServerRequestInterface::class);
        $this->handlerMock    = $this->createMock(RequestHandlerInterface::class);
		$this->responseMMock   = $this->createMock(ResponseInterface::class);
		$this->localesMockk    = $this->createMock(Locales::class);
		$this->configMock     = $this->createMock(Config::class);
        $this->middleware     = new LayoutDataMiddleware();
    }

	/**
	 * @throws CoreException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	#[Group('units')]
    public function testProcessWithUserSession(): void
    {
        $this->requestMock
            ->method('getAttribute')
            ->willReturnCallback(function ($attribute)
            {
                if ($attribute === 'session')
                    return $this->sessionMock;
                elseif ($attribute === 'translator')
                    return $this->translatorMock;
				elseif ($attribute === 'locales')
					return $this->localesMockk;
				elseif ($attribute === 'config')
					return $this->configMock;

                return null;
            });

        $this->sessionMock->method('exists')->with('user')
            ->willReturn(true);

        $this->sessionMock->method('get')->with('user')
            ->willReturn(['username' => 'testuser']);

        $this->translatorMock->method('translateArrayForOptions')
            ->with('languages', 'menu')
            ->willReturn(['en' => 'English', 'de' => 'German']);

		$this->localesMockk->expects($this->once())->method('getLanguageCode')->willReturn('en_US');
		$this->configMock->expects($this->once())->method('getEnv')->with('APP_NAME')->willReturn('garlic-hub');
		$this->handlerMock->method('handle')
            ->willReturn($this->responseMMock);

        $result = $this->middleware->process($this->requestMock, $this->handlerMock);

        $this->assertSame($this->responseMMock, $result);
    }

	/**
	 * @throws CoreException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	#[Group('units')]
    public function testProcessWithoutUserSession(): void
    {
        $this->requestMock
            ->method('getAttribute')
            ->willReturnCallback(function ($attribute)
            {
                if ($attribute === 'session')
                    return $this->sessionMock;
                elseif ($attribute === 'translator')
                    return $this->translatorMock;
				elseif ($attribute === 'locales')
					return $this->localesMockk;
				elseif ($attribute === 'config')
					return $this->configMock;

                return null;
            });

        $this->sessionMock->method('exists')->with('user')
            ->willReturn(false);

        $this->translatorMock
            ->method('translate')
            ->willReturnMap([
                ['login', 'login', 'Login'],
                ['legal_notice', 'menu', 'Legal Notice'],
                ['privacy', 'menu', 'Privacy Policy'],
                ['terms', 'menu', 'Terms'],
            ]);

		$this->localesMockk->expects($this->once())->method('getLanguageCode')->willReturn('en_US');

        $this->translatorMock
            ->method('translateArrayForOptions')
            ->with('languages', 'menu')
            ->willReturn(['en' => 'English', 'de' => 'German']);

        $this->handlerMock
            ->method('handle')
            ->willReturn($this->responseMMock);

        $result = $this->middleware->process($this->requestMock, $this->handlerMock);

        $this->assertSame($this->responseMMock, $result);
    }

}
