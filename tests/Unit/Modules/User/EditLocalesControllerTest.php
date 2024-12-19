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
	private ServerRequestInterface $mockRequest;
	private ResponseInterface $mockResponse;
	private Helper $mockSession;
	private Locales $mockLocales;
	private UserService $mockUserService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->mockRequest     = $this->createMock(ServerRequestInterface::class);
		$this->mockResponse    = $this->createMock(ResponseInterface::class);
		$this->mockSession     = $this->createMock(Helper::class);
		$this->mockLocales     = $this->createMock(Locales::class);
		$this->mockUserService = $this->createMock(UserService::class);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testSetLocales()
	{
		$this->mockRequest->method('getAttribute')
						  ->willReturnCallback(function ($attribute) {
							  return match ($attribute) {
								  'session' => $this->mockSession,
								  'locales' => $this->mockLocales,
								  default => null,
							  };
						  });

		$this->mockSession->method('exists')->with('user')->willReturn(true);
		$this->mockSession->method('get')->with('user')->willReturn(['UID' => 1, 'locale' => 'en_US']);
		$this->mockSession->expects($this->exactly(2))->method('set');

		$this->mockLocales->expects($this->once())->method('determineCurrentLocale');

		$previousUrl = 'some/url/line';
		$this->mockRequest->method('getHeaderLine')->with('Referer')->willReturn($previousUrl);

		$this->mockResponse->expects($this->once())->method('withHeader')->with('Location', $previousUrl)
						   ->willReturn($this->mockResponse);

		$this->mockResponse->expects($this->once())->method('withStatus')
			 ->with(302)->willReturn($this->mockResponse);

		$this->mockUserService->expects($this->once())->method('updateUser')
			 ->with(1, ['locale' => 'de_DE']);


		$controller = new EditLocalesController($this->mockUserService);
		$result = $controller->setLocales($this->mockRequest, $this->mockResponse, ['locale' => 'de_DE']);
		$this->assertSame($this->mockResponse, $result);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testSetLocalesWithBrokenUserArray()
	{
		$this->mockRequest->method('getAttribute')
						  ->willReturnCallback(function ($attribute) {
							  return match ($attribute) {
								  'session' => $this->mockSession,
								  'locales' => $this->mockLocales,
								  default => null,
							  };
						  });
		$this->mockSession->method('get')->with('user')->willReturn('not_an_array');
		$this->mockSession->expects($this->once())->method('set')->with('locale', 'de_DE');

		$this->mockLocales->expects($this->once())->method('determineCurrentLocale');

		$previousUrl = 'some/url/line';
		$this->mockRequest->method('getHeaderLine')->with('Referer')->willReturn($previousUrl);

		$this->mockResponse->expects($this->once())->method('withHeader')->with('Location', $previousUrl)
						   ->willReturn($this->mockResponse);

		$this->mockUserService->expects($this->never())->method('updateUser');
		$this->mockResponse->expects($this->once())->method('withStatus')->with(302)->willReturn($this->mockResponse);

		$controller = new EditLocalesController($this->mockUserService);
		$result = $controller->setLocales($this->mockRequest, $this->mockResponse, ['locale' => 'de_DE']);
		$this->assertSame($this->mockResponse, $result);
	}

}
