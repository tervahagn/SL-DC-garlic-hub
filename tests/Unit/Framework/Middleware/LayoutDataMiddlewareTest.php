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
    private Translator $mockTranslator;
    private Helper $mockSession;
	private Config $mockConfig;
    private ServerRequestInterface $mockRequest;
    private RequestHandlerInterface $mockHandler;
    private ResponseInterface $mockResponse;
	private Locales $mockLocales;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->mockTranslator = $this->createMock(Translator::class);
        $this->mockSession    = $this->createMock(Helper::class);
        $this->mockRequest    = $this->createMock(ServerRequestInterface::class);
        $this->mockHandler    = $this->createMock(RequestHandlerInterface::class);
		$this->mockResponse   = $this->createMock(ResponseInterface::class);
		$this->mockLocales    = $this->createMock(Locales::class);
		$this->mockConfig     = $this->createMock(Config::class);
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
        $this->mockRequest
            ->method('getAttribute')
            ->willReturnCallback(function ($attribute)
            {
                if ($attribute === 'session')
                    return $this->mockSession;
                elseif ($attribute === 'translator')
                    return $this->mockTranslator;
				elseif ($attribute === 'locales')
					return $this->mockLocales;
				elseif ($attribute === 'config')
					return $this->mockConfig;

                return null;
            });

        $this->mockSession->method('exists')->with('user')
            ->willReturn(true);

        $this->mockSession->method('get')->with('user')
            ->willReturn(['username' => 'testuser']);

        $this->mockTranslator->method('translateArrayForOptions')
            ->with('languages', 'menu')
            ->willReturn(['en' => 'English', 'de' => 'German']);

		$this->mockLocales->expects($this->once())->method('getLanguageCode')->willReturn('en_US');
		$this->mockConfig->expects($this->once())->method('getEnv')->with('APP_NAME')->willReturn('garlic-hub');
		$this->mockHandler->method('handle')
            ->willReturn($this->mockResponse);

        $result = $this->middleware->process($this->mockRequest, $this->mockHandler);

        $this->assertSame($this->mockResponse, $result);
    }

	/**
	 * @throws CoreException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	#[Group('units')]
    public function testProcessWithoutUserSession(): void
    {
        $this->mockRequest
            ->method('getAttribute')
            ->willReturnCallback(function ($attribute)
            {
                if ($attribute === 'session')
                    return $this->mockSession;
                elseif ($attribute === 'translator')
                    return $this->mockTranslator;
				elseif ($attribute === 'locales')
					return $this->mockLocales;
				elseif ($attribute === 'config')
					return $this->mockConfig;

                return null;
            });

        $this->mockSession->method('exists')->with('user')
            ->willReturn(false);

        $this->mockTranslator
            ->method('translate')
            ->willReturnMap([
                ['login', 'login', 'Login'],
                ['legal_notice', 'menu', 'Legal Notice'],
                ['privacy', 'menu', 'Privacy Policy'],
                ['terms', 'menu', 'Terms'],
            ]);

		$this->mockLocales->expects($this->once())->method('getLanguageCode')->willReturn('en_US');

        $this->mockTranslator
            ->method('translateArrayForOptions')
            ->with('languages', 'menu')
            ->willReturn(['en' => 'English', 'de' => 'German']);

        $this->mockHandler
            ->method('handle')
            ->willReturn($this->mockResponse);

        $result = $this->middleware->process($this->mockRequest, $this->mockHandler);

        $this->assertSame($this->mockResponse, $result);
    }

}
