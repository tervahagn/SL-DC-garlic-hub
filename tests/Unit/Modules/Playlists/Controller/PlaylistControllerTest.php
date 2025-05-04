<?php

namespace Tests\Unit\Modules\Playlists\Controller;

use App\Framework\Core\Session;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Controller\PlaylistsController;
use App\Modules\Playlists\Helper\Datatable\Parameters;
use App\Modules\Playlists\Services\PlaylistsDatatableService;
use App\Modules\Playlists\Services\PlaylistsService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class PlaylistControllerTest extends TestCase
{
	private readonly PlaylistsController $controller;
	private readonly PlaylistsService $playlistsServiceMock;
	private readonly PlaylistsDatatableService $playlistsDatatableServiceMock;
	private readonly Parameters $parametersMock;
	private readonly ResponseInterface $responseMock;
	private readonly ServerRequestInterface $requestMock;
	private readonly StreamInterface $streamInterfaceMock;
	private readonly Session $sessionMock;
	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->playlistsServiceMock = $this->createMock(PlaylistsService::class);
		$this->playlistsDatatableServiceMock = $this->createMock(PlaylistsDatatableService::class);
		$this->parametersMock       = $this->createMock(Parameters::class);
		$this->requestMock          = $this->createMock(ServerRequestInterface::class);
		$this->responseMock         = $this->createMock(ResponseInterface::class);
		$this->streamInterfaceMock  = $this->createMock(StreamInterface::class);
		$this->sessionMock          = $this->createMock(Session::class);

		$this->controller = new PlaylistsController($this->playlistsServiceMock, $this->playlistsDatatableServiceMock, $this->parametersMock);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDelete(): void
	{
		$post =  ['playlist_id' => 789];
		$this->requestMock->method('getParsedBody')->willReturn($post);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);
		$this->playlistsServiceMock->method('setUID')->with(456);

		$this->playlistsServiceMock->method('delete')->with(789)->willReturn(1);
		$this->mockJsonResponse(['success' => true]);

		$response = $this->controller->delete($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteInvalidPlaylistId(): void
	{
		$post =  [];
		$this->requestMock->method('getParsedBody')->willReturn($post);
		$this->playlistsServiceMock->method('setUID')->with(456);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist ID not valid.']);
		$this->playlistsServiceMock->expects($this->never())->method('delete');

		$response = $this->controller->delete($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteNotFoundPlaylist(): void
	{
		$post =  ['playlist_id' => 12];
		$this->requestMock->method('getParsedBody')->willReturn($post);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);
		$this->playlistsServiceMock->method('setUID')->with(456);

		$this->playlistsServiceMock->method('delete')->with(12)->willReturn(0);
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist not found.']);

		$response = $this->controller->delete($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testToggleShuffle(): void
	{
		$post =  ['playlist_id' => 12];
		$this->requestMock->method('getParsedBody')->willReturn($post);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);
		$this->playlistsServiceMock->method('setUID')->with(456);

		$data = ['affected' => 1, 'playlist_metrics' => ['some_metrics_array']];
		$this->playlistsServiceMock->method('toggleShuffle')->with(12)->willReturn($data);

		$this->mockJsonResponse(['success' => true, 'playlist_metrics' => $data['playlist_metrics']]);

		$response = $this->controller->toggleShuffle($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testToggleShuffleInvalidPlaylistId(): void
	{
		$post =  [];
		$this->requestMock->method('getParsedBody')->willReturn($post);
		$this->playlistsServiceMock->method('setUID')->with(456);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist ID not valid.']);
		$this->playlistsServiceMock->expects($this->never())->method('toggleShuffle');

		$response = $this->controller->toggleShuffle($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testToggleShuffleWhenNotEffecting(): void
	{
		$post =  ['playlist_id' => 12];

		$this->requestMock->method('getParsedBody')->willReturn($post);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);
		$this->playlistsServiceMock->method('setUID')->with(456);

		$data = ['affected' => 0, 'playlist_metrics' => ['some_metrics_array']];
		$this->playlistsServiceMock->method('toggleShuffle')->with(12)->willReturn($data);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist not found.']);

		$response = $this->controller->toggleShuffle($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testShufflePicking(): void
	{
		$post =  ['playlist_id' => 11, 'shuffle_picking' => 4];

		$this->requestMock->method('getParsedBody')->willReturn($post);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);
		$this->playlistsServiceMock->method('setUID')->with(456);

		$data = ['affected' => 1, 'playlist_metrics' => ['some_metrics_array']];
		$this->playlistsServiceMock->method('shufflePicking')->with(11, 4)->willReturn($data);

		$this->mockJsonResponse(['success' => true, 'playlist_metrics' => $data['playlist_metrics']]);

		$response = $this->controller->shufflePicking($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testShufflePickingInvalidPlaylistId(): void
	{
		$post =  ['playlist_id' => 11];
		$this->requestMock->method('getParsedBody')->willReturn($post);

		$this->playlistsServiceMock->expects($this->never())->method('shufflePicking');
		$this->mockJsonResponse(['success' => false, 'error_message' => 'No picking value found.']);

		$response = $this->controller->shufflePicking($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testShufflePickingInvalidPicking(): void
	{
		$post =  ['shuffle_picking' => 4];
		$this->requestMock->method('getParsedBody')->willReturn($post);

		$this->playlistsServiceMock->expects($this->never())->method('shufflePicking');
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$response = $this->controller->shufflePicking($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testShufflePickingPlaylistNotFound(): void
	{
		$post =  ['playlist_id' => 11, 'shuffle_picking' => 4];

		$this->requestMock->method('getParsedBody')->willReturn($post);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);
		$this->playlistsServiceMock->method('setUID')->with(456);

		$data = ['affected' => 0, 'playlist_metrics' => ['some_metrics_array']];
		$this->playlistsServiceMock->method('shufflePicking')->with(11, 4)->willReturn($data);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist not found.']);

		$response = $this->controller->shufflePicking($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testLoadZoneSucceed(): void
	{
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);

		$this->playlistsServiceMock->expects($this->once())->method('setUID')->with(456);
		$this->playlistsServiceMock->expects($this->once())->method('loadPlaylistForMultizone')
			->with(14)->willReturn(['some_zone_stuff']);

		$this->mockJsonResponse(['success' => true, 'zones' => ['some_zone_stuff']]);

		$this->playlistsServiceMock->expects($this->once())->method('hasErrorMessages')->willReturn(false);
		$this->playlistsServiceMock->expects($this->never())->method('getErrorMessages');

		$response = $this->controller->loadZone($this->requestMock, $this->responseMock, ['playlist_id' => 14]);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testLoadZoneInvalidPlaylistId(): void
	{
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->playlistsServiceMock->expects($this->never())->method('setUID');
		$this->playlistsServiceMock->expects($this->never())->method('loadPlaylistForMultizone');

		$response = $this->controller->loadZone($this->requestMock, $this->responseMock, ['playlist_id' => 0]);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testLoadZoneErrors(): void
	{
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);

		$this->playlistsServiceMock->expects($this->once())->method('setUID')->with(456);
		$this->playlistsServiceMock->expects($this->once())->method('loadPlaylistForMultizone')
			->with(14)->willReturn(['some_zone_stuff']);

		$this->playlistsServiceMock->method('hasErrorMessages')->willReturn(true);

		$this->playlistsServiceMock->expects($this->once())->method('getErrorMessages')->willReturn( ['errors']);
		$this->mockJsonResponse(['success' => false, 'error_message' => ['errors']]);

		$response = $this->controller->loadZone($this->requestMock, $this->responseMock, ['playlist_id' => 14]);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testSaveZoneSucceed(): void
	{
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);

		$this->playlistsServiceMock->expects($this->once())->method('setUID')->with(456);

		$this->requestMock->method('getParsedBody')->willReturn(['save_zone_stuff']);
		$this->playlistsServiceMock->expects($this->once())->method('saveZones')
			->with(14, ['save_zone_stuff'])->willReturn(1);

		$this->mockJsonResponse(['success' => true]);

		$response = $this->controller->saveZone($this->requestMock, $this->responseMock, ['playlist_id' => 14]);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testSaveZoneInvalidPlaylistId(): void
	{
		$this->playlistsServiceMock->expects($this->never())->method('setUID');
		$this->requestMock->expects($this->never())->method('getParsedBody');
		$this->playlistsServiceMock->expects($this->never())->method('loadPlaylistForMultizone');

		$this->playlistsServiceMock->expects($this->never())->method('saveZones');
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist ID not valid.']);


		$response = $this->controller->saveZone($this->requestMock, $this->responseMock, ['playlist_id' => 0]);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testNotSaveZone(): void
	{
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);

		$this->playlistsServiceMock->expects($this->once())->method('setUID')->with(456);

		$this->requestMock->method('getParsedBody')->willReturn(['save_zone_stuff']);
		$this->playlistsServiceMock->expects($this->once())->method('saveZones')
			->with(14, ['save_zone_stuff'])->willReturn(0);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Multizone could not be saved']);

		$response = $this->controller->saveZone($this->requestMock, $this->responseMock, ['playlist_id' => 14]);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindByName(): void
	{
		$this->parametersMock->expects($this->once())->method('setUserInputs')->with(['name' => 'play']);
		$this->parametersMock->expects($this->once())->method('parseInputAllParameters');

		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);
		$this->playlistsDatatableServiceMock->expects($this->once())->method('setUID')->with(456);

		$this->playlistsDatatableServiceMock->expects($this->once())->method('loadDatatable');
		$playlists = [
			['playlist_id' => 1, 'playlist_name' => 'playlist1', 'description' => 'description1'],
			['playlist_id' => 2, 'playlist_name' => 'playlist2', 'description' => 'description2'],
			['playlist_id' => 3, 'playlist_name' => 'playlist3', 'description' => 'description3'],
			['playlist_id' => 4, 'playlist_name' => 'playlist4', 'description' => 'description4'],
		];

		$this->playlistsDatatableServiceMock->expects($this->once())->method('getCurrentFilterResults')->willReturn($playlists);

		$output = [
			['id' => 1, 'name' => 'playlist1'],
			['id' => 2, 'name' => 'playlist2'],
			['id' => 3, 'name' => 'playlist3'],
			['id' => 4, 'name' => 'playlist4']
		];

		$this->mockJsonResponse($output);

		$response = $this->controller->findByName($this->requestMock, $this->responseMock, ['name' => 'play']);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testFindForPlayerAssignment(): void
	{
		$arg =  ['playlist_id' => 11];
		$args = ['playlist_id' => 11, 'playlist_mode' => 'master,multizone'];

		$this->parametersMock->expects($this->once())->method('setUserInputs')->with($args);
		$this->parametersMock->expects($this->once())->method('parseInputAllParameters');

		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);
		$this->playlistsDatatableServiceMock->expects($this->once())->method('setUID')->with(456);

		$this->playlistsDatatableServiceMock->expects($this->once())->method('loadDatatable');
		$playlists = [
			['playlist_id' => 1, 'playlist_name' => 'playlist1', 'description' => 'description1'],
			['playlist_id' => 2, 'playlist_name' => 'playlist2', 'description' => 'description2']
		];

		$this->playlistsDatatableServiceMock->expects($this->once())->method('getCurrentFilterResults')->willReturn($playlists);

		$output = [
			['id' => 1, 'name' => 'playlist1'],
			['id' => 2, 'name' => 'playlist2']
		];

		$this->mockJsonResponse($output);

		$response = $this->controller->findForPlayerAssignment($this->requestMock, $this->responseMock, $arg);
		$this->assertInstanceOf(ResponseInterface::class, $response);

	}


	#[Group('units')]
	public function testFindById(): void
	{
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);
		$this->playlistsServiceMock->expects($this->once())->method('setUID')->with(456);

		$this->playlistsServiceMock->expects($this->once())->method('loadNameById')
			->with(14)->willReturn(['playlist_name']);

		$this->mockJsonResponse(['playlist_name']);

		$response = $this->controller->findById($this->requestMock, $this->responseMock, ['playlist_id' => 14]);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testFindByIdFail(): void
	{
		$this->playlistsServiceMock->expects($this->never())->method('loadNameById');

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$response = $this->controller->findById($this->requestMock, $this->responseMock, []);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}


	private function mockJsonResponse(array $data): void
	{
		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);
		$this->streamInterfaceMock->method('write')->with(json_encode($data));
		$this->responseMock->expects($this->once())->method('withHeader')
			->with('Content-Type', 'application/json')
			->willReturnSelf();
		$this->responseMock->method('withStatus')->with('200');
	}
}
