<?php

namespace Tests\Controller;

use App\Controller\HomeController;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use SlimSession\Helper;

class HomeControllerTest extends TestCase
{
	#[Group('units')]
	/**
	 * @throws Exception
	 */
	public function testIndexRedirectsToLoginIfUserNotInSession(): void
	{
		$request  = $this->createMock(ServerRequestInterface::class);
		$response = $this->createMock(ResponseInterface::class);
		$session  = $this->createMock(Helper::class);

		$request->method('getAttribute')->with('session')->willReturn($session);
		$session->method('exists')->with('user')->willReturn(false);
		$response->expects($this->once())->method('withHeader')->with('Location', '/login')->willReturnSelf();
		$response->expects($this->once())->method('withStatus')->with(302)->willReturnSelf();

		$controller = new HomeController();
		$result = $controller->index($request, $response);

		$this->assertSame($response, $result);
	}

	#[Group('units')]
	/**
	 * @throws Exception
	 */
	public function testIndexReturnsHomePageIfUserInSession(): void
	{
		$request  = $this->createMock(ServerRequestInterface::class);
		$response = $this->createMock(ResponseInterface::class);
		$session  = $this->createMock(Helper::class);

		$request->method('getAttribute')->with('session')->willReturn($session);
		$session->method('exists')->with('user')->willReturn(true);
		$session->method('get')->with('user')->willReturn(['username' => 'testuser']);
		$response->method('getBody')->willReturn($this->createMock(StreamInterface::class));
		$response->expects($this->once())->method('withHeader')->with('Content-Type', 'text/html')->willReturnSelf();

		$controller = new HomeController();
		$result = $controller->index($request, $response);

		$this->assertSame($response, $result);
	}
}
