<?php

namespace Tests\Unit\Modules\Playlists\Controller;

use App\Framework\Core\Session;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Modules\Playlists\Controller\ShowComposeController;
use App\Modules\Playlists\Helper\Compose\UiTemplatesPreparer;
use App\Modules\Playlists\Services\PlaylistsService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Slim\Flash\Messages;

class ShowComposeControllerTest extends TestCase
{
	private readonly ResponseInterface&MockObject $responseMock;
	private readonly ServerRequestInterface&MockObject $requestMock;
	private readonly StreamInterface&MockObject $streamInterfaceMock;
	private readonly PlaylistsService&MockObject $playlistsServiceMock;
	private readonly UiTemplatesPreparer&MockObject $uiTemplatesPreparerMock;
	private Messages &MockObject$flashMock;
	private readonly Session&MockObject $sessionMock;
	private readonly ShowComposeController $controller;


	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->requestMock             = $this->createMock(ServerRequestInterface::class);
		$this->responseMock            = $this->createMock(ResponseInterface::class);
		$this->streamInterfaceMock     = $this->createMock(StreamInterface::class);
		$this->sessionMock             = $this->createMock(Session::class);
		$this->playlistsServiceMock    = $this->createMock(PlaylistsService::class);
		$this->uiTemplatesPreparerMock = $this->createMock(UiTemplatesPreparer::class);
		$this->flashMock               = $this->createMock(Messages::class);

		$this->requestMock->method('getAttribute')->willReturnMap([
			['session', $this->sessionMock],
			['flash', $this->flashMock]
		]);
		$this->sessionMock->method('get')->willReturn(['UID' => 123]);
		$this->playlistsServiceMock->expects($this->once())->method('setUID')->with(123);

		$this->controller = new ShowComposeController($this->playlistsServiceMock, $this->uiTemplatesPreparerMock);
	}

	/**
	 * @throws Exception
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testShowRedirectsWhenPlaylistIdIsInvalid(): void
	{
		$this->outputSimpleErrorMock('Playlist ID not valid.');
		$this->playlistsServiceMock->expects($this->never())->method('loadPlaylistForEdit');

		$response = $this->controller->show($this->requestMock, $this->responseMock, []);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws CoreException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testShowRedirectsWhenPlaylistIsEmpty(): void
	{
		$this->playlistsServiceMock->method('loadPlaylistForEdit')->willReturn([]);

		$this->outputSimpleErrorMock('Playlist not found.');

		$response = $this->controller->show($this->requestMock, $this->responseMock,  ['playlist_id' => 1]);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testShowCallsBuildMultizoneEditorForMultizoneMode(): void
	{
		$playlist = ['playlist_mode' => 'multizone'];
		$this->playlistsServiceMock->method('loadPlaylistForEdit')->willReturn($playlist);
		$data = ['uiTemplates' => 'someData'];
		$this->uiTemplatesPreparerMock->expects($this->once())->method('buildMultizoneEditor')
			->with($playlist)
			->willReturn($data);

		$this->outputStandard($data);
		$response = $this->controller->show($this->requestMock, $this->responseMock, ['playlist_id' => 1]);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testShowCallsBuildMultizoneEditorForExternalMode(): void
	{
		$playlist = ['playlist_mode' => 'external'];
		$this->playlistsServiceMock->method('loadPlaylistForEdit')->willReturn($playlist);
		$data = ['uiTemplates' => 'someData'];
		$this->uiTemplatesPreparerMock->expects($this->once())->method('buildExternalEditor')
			->with($playlist)
			->willReturn($data);

		$this->outputStandard($data);
		$response = $this->controller->show($this->requestMock, $this->responseMock, ['playlist_id' => 1]);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testShowCallsBuildMultizoneEditorForStandards(): void
	{
		$playlist = ['playlist_mode' => 'master'];
		$this->playlistsServiceMock->method('loadPlaylistForEdit')->willReturn($playlist);
		$data = ['uiTemplates' => 'someData'];
		$this->uiTemplatesPreparerMock->expects($this->once())->method('buildCircularEditor')
			->with($playlist)
			->willReturn($data);

		$this->outputStandard($data);
		$response = $this->controller->show($this->requestMock, $this->responseMock, ['playlist_id' => 1]);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}


	#[Group('units')]
	public function testShowCallsUnsupported(): void
	{
		$playlist = ['playlist_mode' => 'unsupported'];
		$this->playlistsServiceMock->method('loadPlaylistForEdit')->willReturn($playlist);

		$this->outputSimpleErrorMock('Unsupported playlist mode: .'.$playlist['playlist_mode']);
		$response = $this->controller->show($this->requestMock, $this->responseMock, ['playlist_id' => 1]);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}


	#[Group('units')]
	public function testShowFlashErrors(): void
	{
		$this->playlistsServiceMock->method('hasErrorMessages')->willReturn(true);
		$this->playlistsServiceMock->method('getErrorMessages')->willReturn(['error1', 'error2']);

		$this->flashMock->expects($this->exactly(2))->method('addMessage')
			->willReturnMap([
				['error', 'error1'],
				['error', 'error2']
			]);

		$this->responseMock->method('withHeader')->with('Location', '/playlists')->willReturnSelf();
		$this->responseMock->method('withStatus')->with('302');

		$response = $this->controller->show($this->requestMock, $this->responseMock, []);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws Exception
	 */
	private function outputSimpleErrorMock(string $errorMessage): void
	{
		$this->playlistsServiceMock->method('hasErrorMessages')->willReturn(false);
		$this->flashMock->expects($this->once())->method('addMessage')->with('error', $errorMessage);

		$this->responseMock->method('withHeader')->with('Location', '/playlists')->willReturnSelf();
		$this->responseMock->method('withStatus')->with(302);
	}

	private function outputStandard(array $data): void
	{
		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);
		$this->streamInterfaceMock->method('write')->with(serialize($data));
		$this->responseMock->method('withHeader')->with('Content-Type', 'text/html')->willReturnSelf();
		$this->responseMock->method('withStatus')->with(200);
	}


}
