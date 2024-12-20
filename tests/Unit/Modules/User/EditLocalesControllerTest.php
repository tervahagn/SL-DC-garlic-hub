<?php

namespace Tests\Unit\Modules\User;

use App\Framework\Core\Locales\Locales;
use App\Framework\User\UserService;
use App\Modules\User\EditLocalesController;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlimSession\Helper;

class EditLocalesControllerTest extends TestCase
{
	private ServerRequestInterface $requestMock;
	private ResponseInterface $responseMock;
	private Helper $sessionMock;
	private Locales $localesMock;
	private UserService $userServiceMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->requestMock     = $this->createMock(ServerRequestInterface::class);
		$this->responseMock    = $this->createMock(ResponseInterface::class);
		$this->sessionMock     = $this->createMock(Helper::class);
		$this->localesMock     = $this->createMock(Locales::class);
		$this->userServiceMock = $this->createMock(UserService::class);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testSetLocales()
	{
		$this->requestMock->method('getAttribute')
						  ->willReturnCallback(function ($attribute) {
							  return match ($attribute) {
								  'session' => $this->sessionMock,
								  'locales' => $this->localesMock,
								  default => null,
							  };
						  });

		$this->sessionMock->method('exists')->with('user')->willReturn(true);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 1, 'locale' => 'en_US']);
		$this->sessionMock->expects($this->exactly(2))->method('set');

		$this->localesMock->expects($this->once())->method('determineCurrentLocale');

		$previousUrl = 'some/url/line';
		$this->requestMock->method('getHeaderLine')->with('Referer')->willReturn($previousUrl);

		$this->responseMock->expects($this->once())->method('withHeader')->with('Location', $previousUrl)
						   ->willReturn($this->responseMock);

		$this->responseMock->expects($this->once())->method('withStatus')
						   ->with(302)->willReturn($this->responseMock);

		$this->userServiceMock->expects($this->once())->method('updateUser')
			 ->with(1, ['locale' => 'de_DE']);


		$controller = new EditLocalesController($this->userServiceMock);
		$result = $controller->setLocales($this->requestMock, $this->responseMock, ['locale' => 'de_DE']);
		$this->assertSame($this->responseMock, $result);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testSetLocalesWithBrokenUserArray()
	{
		$this->requestMock->method('getAttribute')
						  ->willReturnCallback(function ($attribute) {
							  return match ($attribute) {
								  'session' => $this->sessionMock,
								  'locales' => $this->localesMock,
								  default => null,
							  };
						  });
		$this->sessionMock->method('get')->with('user')->willReturn('not_an_array');
		$this->sessionMock->expects($this->once())->method('set')->with('locale', 'de_DE');

		$this->localesMock->expects($this->once())->method('determineCurrentLocale');

		$previousUrl = 'some/url/line';
		$this->requestMock->method('getHeaderLine')->with('Referer')->willReturn($previousUrl);

		$this->responseMock->expects($this->once())->method('withHeader')->with('Location', $previousUrl)
						   ->willReturn($this->responseMock);

		$this->userServiceMock->expects($this->never())->method('updateUser');
		$this->responseMock->expects($this->once())->method('withStatus')->with(302)->willReturn($this->responseMock);

		$controller = new EditLocalesController($this->userServiceMock);
		$result = $controller->setLocales($this->requestMock, $this->responseMock, ['locale' => 'de_DE']);
		$this->assertSame($this->responseMock, $result);
	}

}
