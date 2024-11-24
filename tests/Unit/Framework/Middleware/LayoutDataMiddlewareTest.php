<?php

namespace Tests\Unit\Framework\Middleware;

use App\Framework\Core\Translate\Translator;
use App\Framework\Middleware\LayoutDataMiddleware;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlimSession\Helper;

class LayoutDataMiddlewareTest extends TestCase
{
    private LayoutDataMiddleware $middleware;
    private Translator $translatorMock;
    private Helper $sessionMock;
    private ServerRequestInterface $requestMock;
    private RequestHandlerInterface $handlerMock;
    private ResponseInterface $responseMock;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->translatorMock = $this->createMock(Translator::class);
        $this->sessionMock    = $this->createMock(Helper::class);
        $this->requestMock    = $this->createMock(ServerRequestInterface::class);
        $this->handlerMock    = $this->createMock(RequestHandlerInterface::class);
        $this->responseMock   = $this->createMock(ResponseInterface::class);

        $this->middleware = new LayoutDataMiddleware();
    }

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

                return null;
            });

        $this->sessionMock
            ->method('exists')
            ->with('user')
            ->willReturn(true);

        $this->sessionMock
            ->method('get')
            ->with('user')
            ->willReturn(['username' => 'testuser']);

        $this->translatorMock
            ->method('translateArrayForOptions')
            ->with('languages', 'menu')
            ->willReturn(['en' => 'English', 'de' => 'German']);

        $this->handlerMock
            ->method('handle')
            ->willReturn($this->responseMock);

        $result = $this->middleware->process($this->requestMock, $this->handlerMock);

        $this->assertSame($this->responseMock, $result);
    }

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

                return null;
            });

        $this->sessionMock
            ->method('exists')
            ->with('user')
            ->willReturn(false);

        $this->translatorMock
            ->method('translate')
            ->willReturnMap([
                ['login', 'login', 'Login'],
                ['legal_notice', 'menu', 'Legal Notice'],
                ['privacy', 'menu', 'Privacy Policy'],
                ['terms', 'menu', 'Terms'],
            ]);

        $this->translatorMock
            ->method('translateArrayForOptions')
            ->with('languages', 'menu')
            ->willReturn(['en' => 'English', 'de' => 'German']);

        $this->handlerMock
            ->method('handle')
            ->willReturn($this->responseMock);

        $result = $this->middleware->process($this->requestMock, $this->handlerMock);

        $this->assertSame($this->responseMock, $result);
    }

}
