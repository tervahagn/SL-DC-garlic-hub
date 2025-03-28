<?php

namespace Tests\Unit\Modules\Playlists\Helper\Settings;

use App\Framework\Core\Session;
use App\Modules\Playlists\Helper\Settings\Builder;
use App\Modules\Playlists\Helper\Settings\Facade;
use App\Modules\Playlists\Helper\Settings\Parameters;
use App\Modules\Playlists\Helper\Settings\TemplateRenderer;
use App\Modules\Playlists\Services\PlaylistsService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class FacadeTest extends TestCase
{
	private readonly Builder $settingsFormBuilder;
	private readonly PlaylistsService $playlistsService;
	private readonly Parameters $settingsParameters;
	private readonly TemplateRenderer $renderer;
	private readonly Facade $facade;

	protected function setUp(): void
	{
		$this->settingsFormBuilder = $this->createMock(Builder::class);
		$this->playlistsService    = $this->createMock(PlaylistsService::class);
		$this->settingsParameters  = $this->createMock(Parameters::class);
		$this->renderer            = $this->createMock(TemplateRenderer::class);
		$this->facade = new Facade(
			$this->settingsFormBuilder,
			$this->playlistsService,
			$this->settingsParameters,
			$this->renderer
		);
	}

	#[Group('units')]
	public function testInit(): void
	{
		$sessionMock = $this->createMock(Session::class);

		$sessionMock->expects($this->once())
			->method('get')
			->with('user')
			->willReturn(['UID' => 123]);

		$this->settingsFormBuilder->expects($this->once())
			->method('init')
			->with($sessionMock);

		$this->playlistsService->expects($this->once())
			->method('setUID')
			->with(123);

		$this->facade->init($sessionMock);
	}

	#[Group('units')]
	public function testLoadPlaylistForEdit(): void
	{
		$playlistId = 42;
		$expectedPlaylist = ['id' => $playlistId, 'name' => 'My Playlist'];

		$this->playlistsService->expects($this->once())
			->method('loadPlaylistForEdit')
			->with($playlistId)
			->willReturn($expectedPlaylist);

		$result = $this->facade->loadPlaylistForEdit($playlistId);

		$this->assertSame($expectedPlaylist, $result);
	}

	#[Group('units')]
	public function testConfigurePlaylistFormParameterWithPlaylistId(): void
	{
		$post = ['playlist_id' => 42, 'other_key' => 'value'];
		$expectedPlaylist = ['id' => 42, 'name' => 'My Playlist'];
		$expectedResult = ['processed_input_key' => 'processed_value'];

		$this->playlistsService->expects($this->once())
			->method('loadPlaylistForEdit')
			->with(42)
			->willReturn($expectedPlaylist);

		$this->settingsFormBuilder->expects($this->once())
			->method('configEditParameter')
			->with($expectedPlaylist);

		$this->settingsFormBuilder->expects($this->once())
			->method('handleUserInput')
			->with($post)
			->willReturn($expectedResult);

		$result = $this->facade->configurePlaylistFormParameter($post);

		$this->assertSame($expectedResult, $result);
	}

	#[Group('units')]
	public function testConfigurePlaylistFormParameterWithoutPlaylistId(): void
	{
		$post = ['playlist_mode' => 'new_mode', 'other_key' => 'value'];
		$expectedResult = ['processed_input_key' => 'processed_value'];

		$this->settingsFormBuilder->expects($this->once())
			->method('configNewParameter')
			->with('new_mode');

		$this->settingsFormBuilder->expects($this->once())
			->method('handleUserInput')
			->with($post)
			->willReturn($expectedResult);

		$result = $this->facade->configurePlaylistFormParameter($post);

		$this->assertSame($expectedResult, $result);
	}

	#[Group('units')]
	public function testStorePlaylistWithExistingId(): void
	{
		$post = ['playlist_id' => 42, 'name' => 'Updated Playlist'];
		$expectedKeys = ['id', 'name'];
		$expectedValues = [42, 'Updated Playlist'];
		$expectedId = 42;

		$this->settingsParameters->expects($this->once())
			->method('getInputParametersKeys')
			->willReturn($expectedKeys);

		$this->settingsParameters->expects($this->once())
			->method('getInputValuesArray')
			->willReturn($expectedValues);

		$this->playlistsService->expects($this->once())
			->method('update')
			->with(['id' => 42, 'name' => 'Updated Playlist'])
			->willReturn($expectedId);

		$result = $this->facade->storePlaylist($post);

		$this->assertSame($expectedId, $result);
	}

	#[Group('units')]
	public function testStorePlaylistWithoutId(): void
	{
		$post = ['name' => 'New Playlist'];
		$expectedKeys = ['name'];
		$expectedValues = ['New Playlist'];
		$expectedId = 99;

		$this->settingsParameters->expects($this->once())
			->method('getInputParametersKeys')
			->willReturn($expectedKeys);

		$this->settingsParameters->expects($this->once())
			->method('getInputValuesArray')
			->willReturn($expectedValues);

		$this->playlistsService->expects($this->once())
			->method('createNew')
			->with(['name' => 'New Playlist'])
			->willReturn($expectedId);

		$result = $this->facade->storePlaylist($post);

		$this->assertSame($expectedId, $result);
	}

	#[Group('units')]
	public function testBuildCreateNewParameter(): void
	{
		$playlistMode = 'new_mode';

		$this->settingsFormBuilder->expects($this->once())
			->method('configNewParameter')
			->with($playlistMode);

		$this->facade->buildCreateNewParameter($playlistMode);
	}

	#[Group('units')]
	public function testBuildEditParameter(): void
	{
		$playlist = ['id' => 123, 'name' => 'Test Playlist'];

		$this->settingsFormBuilder->expects($this->once())
			->method('configEditParameter')
			->with($playlist);

		$this->facade->buildEditParameter($playlist);
	}

	#[Group('units')]
	public function testRender(): void
	{
		$post = ['playlist_mode' => 'edit', 'name' => 'Test Playlist'];
		$expectedElements = ['element1' => 'value1', 'element2' => 'value2'];
		$expectedResult = ['rendered_html' => true];

		$this->settingsFormBuilder->expects($this->once())
			->method('buildForm')
			->with($post)
			->willReturn($expectedElements);

		$this->renderer->expects($this->once())
			->method('renderTemplate')
			->with($expectedElements, $post['playlist_mode'])
			->willReturn($expectedResult);

		$result = $this->facade->render($post);

		$this->assertSame($expectedResult, $result);
	}
}
