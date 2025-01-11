<?php

namespace Tests\Unit\Framework\Middleware;

use App\Framework\Core\Config\Config;
use App\Framework\Core\Locales\Locales;
use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Middleware\LayoutDataMiddleware;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Slim\Flash\Messages;

class LayoutDataMiddlewareTest extends TestCase
{
    private LayoutDataMiddleware $middleware;
    private Translator $translatorMock;
    private Session $sessionMock;
	private Config $configMock;
    private ServerRequestInterface $requestMock;
    private RequestHandlerInterface $handlerMock;
    private ResponseInterface $responseMock;
	private Locales $localesMock;
	private Messages $flashMock;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->translatorMock = $this->createMock(Translator::class);
        $this->sessionMock    = $this->createMock(Session::class);
        $this->requestMock    = $this->createMock(ServerRequestInterface::class);
        $this->handlerMock    = $this->createMock(RequestHandlerInterface::class);
		$this->responseMock   = $this->createMock(ResponseInterface::class);
		$this->localesMock    = $this->createMock(Locales::class);
		$this->configMock     = $this->createMock(Config::class);
		$this->flashMock 	  = $this->createMock(Messages::class);
        $this->middleware     = new LayoutDataMiddleware();
    }

	/**
	 * @throws CoreException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException|PhpfastcacheSimpleCacheException
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
					return $this->localesMock;
				elseif ($attribute === 'config')
					return $this->configMock;
				elseif ($attribute === 'flash')
					return $this->flashMock;

                return null;
            });

		$this->flashMock->expects($this->exactly(2))->method('hasMessage')->willReturn(false);
		$this->flashMock->expects($this->never())->method('getMessage');

        $this->sessionMock->method('exists')->with('user')
            ->willReturn(true);

        $this->sessionMock->method('get')->with('user')
            ->willReturn(['username' => 'testuser']);

        $this->translatorMock->method('translateArrayForOptions')
            ->with('languages', 'menu')
            ->willReturn(['en' => 'English', 'de' => 'German']);

		$this->localesMock->expects($this->once())->method('getLanguageCode')->willReturn('en_US');
		$this->configMock->expects($this->once())->method('getEnv')->with('APP_NAME')->willReturn('garlic-hub');
		$this->handlerMock->method('handle')->willReturn($this->responseMock);

        $result = $this->middleware->process($this->requestMock, $this->handlerMock);

        $this->assertSame($this->responseMock, $result);
    }

	/**
	 * @throws CoreException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException|PhpfastcacheSimpleCacheException
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
					return $this->localesMock;
				elseif ($attribute === 'config')
					return $this->configMock;
				elseif ($attribute === 'flash')
					return $this->flashMock;

                return null;
            });

		$this->flashMock->expects($this->exactly(2))->method('hasMessage')->willReturn(false);
		$this->flashMock->expects($this->never())->method('getMessage');

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

		$this->localesMock->expects($this->once())->method('getLanguageCode')->willReturn('en_US');

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

	/**
	 * @throws CoreException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException|PhpfastcacheSimpleCacheException
	 */
	#[Group('units')]
	public function testProcessWithMessage(): void
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
					return $this->localesMock;
				elseif ($attribute === 'config')
					return $this->configMock;
				elseif ($attribute === 'flash')
					return $this->flashMock;

				return null;
			});

		$this->sessionMock->method('exists')->with('user')->willReturn(false);
		$this->translatorMock
			->method('translate')
			->willReturnMap([
				['login', 'login', 'Login'],
				['legal_notice', 'menu', 'Legal Notice'],
				['privacy', 'menu', 'Privacy Policy'],
				['terms', 'menu', 'Terms'],
			]);

		$this->localesMock->expects($this->once())->method('getLanguageCode')->willReturn('en_US');

		$this->translatorMock->method('translateArrayForOptions')
			->with('languages', 'menu')
			->willReturn(['en' => 'English', 'de' => 'German']);

		$this->handlerMock->method('handle')->willReturn($this->responseMock);
		$this->configMock->expects($this->once())->method('getEnv')->with('APP_NAME')->willReturn('garlic-hub');

		$this->flashMock->method('hasMessage')->willReturn(true);
		$this->flashMock->expects($this->exactly(2))->method('getMessage')
			->willReturnCallback(function ($param)
			{
				return match ($param)
				{
					'error' => ['Error message'],
					'success' => ['Success message'],
					default => null,
				};
			});


		$layoutData = [
			'main_menu'            => [['URL' => '/login', 'LANG_MENU_POINT' => 'Login']],
			'CURRENT_LOCALE_LOWER' => 'en_us',
			'CURRENT_LOCALE_UPPER' => 'EN_US',
			'language_select'      => [
				['LOCALE_LONG' => 'en', 'LOCALE_SMALL' => 'en', 'LANGUAGE_NAME' => 'English'],
				['LOCALE_LONG' => 'de', 'LOCALE_SMALL' => 'de', 'LANGUAGE_NAME' => 'German']
			],
			'user_menu'            => [],

			'APP_NAME'          => 'garlic-hub',
			'messages'          => [
				['MESSAGE_TYPE' => 'error', 'has_close_button' => true, 'MESSAGE_TEXT' => 'Error message'],
				['MESSAGE_TYPE' => 'success', 'has_close_button' => false, 'MESSAGE_TEXT' => 'Success message']
			],
			'LANG_LEGAL_NOTICE' => 'Legal Notice',
			'LANG_PRIVACY'      => 'Privacy Policy',
			'LANG_TERMS'        => 'Terms'
		];


		$this->requestMock->expects($this->once())->method('withAttribute')
			 ->with('layoutData', $layoutData)->willReturnSelf();

		$result = $this->middleware->process($this->requestMock, $this->handlerMock);

		$this->assertSame($this->responseMock, $result);
	}

}
