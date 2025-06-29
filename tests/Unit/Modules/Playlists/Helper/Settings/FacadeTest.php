<?php

namespace Tests\Unit\Modules\Playlists\Helper\Settings;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Helper\PlaylistMode;
use App\Modules\Playlists\Helper\Settings\Builder;
use App\Modules\Playlists\Helper\Settings\Facade;
use App\Modules\Playlists\Helper\Settings\Parameters;
use App\Modules\Playlists\Services\PlaylistsService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;

class FacadeTest extends TestCase
{
	private Builder&MockObject $settingsFormBuilderMock;
	private PlaylistsService&MockObject $playlistsService;
	private Parameters&MockObject $settingsParameters;
	private Translator&MockObject $translatorMock;
	private Facade $facade;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->settingsFormBuilderMock = $this->createMock(Builder::class);
		$this->playlistsService    = $this->createMock(PlaylistsService::class);
		$this->settingsParameters  = $this->createMock(Parameters::class);
		$this->translatorMock      = $this->createMock(Translator::class);
		$this->facade = new Facade(
			$this->settingsFormBuilderMock,
			$this->playlistsService,
			$this->settingsParameters,
		);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testInit(): void
	{
		$sessionMock = $this->createMock(Session::class);

		$sessionMock->expects($this->once())
			->method('get')
			->with('user')
			->willReturn(['UID' => 123]);

		$this->settingsFormBuilderMock->expects($this->once())
			->method('init')
			->with($sessionMock);

		$this->playlistsService->expects($this->once())
			->method('setUID')
			->with(123);

		$this->facade->init($this->translatorMock, $sessionMock);
	}

	/**
	 */
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

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testConfigurePlaylistFormParameterWithPlaylistId(): void
	{
		$post = ['playlist_id' => 42, 'playlist_name' => 'hurz', 'playlist_mode' => PlaylistMode::MASTER->value, 'other_key' => 'value'];
		$expectedPlaylist = ['id' => 42, 'name' => 'My Playlist'];
		$expectedResult = ['processed_input_key' => 'processed_value'];

		$this->playlistsService->expects($this->once())
			->method('loadPlaylistForEdit')
			->with(42)
			->willReturn($expectedPlaylist);

		$this->settingsFormBuilderMock->expects($this->once())
			->method('configEditParameter')
			->with($expectedPlaylist);

		$this->settingsFormBuilderMock->expects($this->once())
			->method('handleUserInput')
			->with($post)
			->willReturn($expectedResult);

		$result = $this->facade->configurePlaylistFormParameter($post);

		$this->assertSame($expectedResult, $result);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testConfigurePlaylistFormParameterWithoutPlaylistId(): void
	{
		$post = ['playlist_mode' => 'new_mode', 'playlist_name' => 'Name', 'other_key' => 'value'];
		$expectedResult = ['processed_input_key' => 'processed_value'];

		$this->settingsFormBuilderMock->expects($this->once())
			->method('configNewParameter')
			->with('new_mode');

		$this->settingsFormBuilderMock->expects($this->once())
			->method('handleUserInput')
			->with($post)
			->willReturn($expectedResult);

		$result = $this->facade->configurePlaylistFormParameter($post);

		$this->assertSame($expectedResult, $result);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
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
			->method('updateSecure')
			->with(['id' => 42, 'name' => 'Updated Playlist'])
			->willReturn($expectedId);

		$result = $this->facade->storePlaylist($post);

		$this->assertSame($expectedId, $result);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
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

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testBuildCreateNewParameter(): void
	{
		$playlistMode = 'new_mode';

		$this->settingsFormBuilderMock->expects($this->once())
			->method('configNewParameter')
			->with($playlistMode);

		$this->facade->buildCreateNewParameter($playlistMode);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testBuildEditParameter(): void
	{
		$playlist = ['UID' => 123, 'company_id' => 1, 'playlist_id' => 12, 'playlist_mode' => PlaylistMode::MASTER->value, 'playlist_name' => 'Test Playlist'];

		$this->settingsFormBuilderMock->expects($this->once())
			->method('configEditParameter')
			->with($playlist);

		$this->facade->buildEditParameter($playlist);
	}

	/**
	 * @throws ModuleException
	 * @throws Exception
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testPrepareUITemplateWithValidMode(): void
	{
		$post = ['playlist_mode' => 'create', 'some_key' => 'value'];
		$translatedTitle = 'Playlists Settings - Create';
		$formData = ['field_1' => 'value_1', 'field_2' => 'value_2'];
		$expectedResult = array_merge($formData, [
			'title' => $translatedTitle,
			'additional_css' => ['/css/playlists/settings.css'],
			'footer_modules' => ['/js/playlists/settings/init.js'],
			'template_name' => 'playlists/edit',
			'form_action' => '/playlists/settings',
			'save_button_label' => 'Save',
		]);

		$sessionMock = $this->createMock(Session::class);
		$sessionMock->expects($this->once())->method('get')->willReturn(['UID' => 123]);
		$this->facade->init($this->translatorMock, $sessionMock);

		$this->translatorMock->expects($this->exactly(2))
			->method('translate')
			->willReturnMap([
				['settings', 'playlists', [], 'Playlists Settings'],
				['save', 'main', [], 'Save']
			]);

		$this->translatorMock->expects($this->once())
			->method('translateArrayForOptions')
			->with('playlist_mode_selects', 'playlists')
			->willReturn(['create' => 'Create']);

		$this->settingsFormBuilderMock->expects($this->once())
			->method('buildForm')
			->with($post)
			->willReturn($formData);

		$result = $this->facade->prepareUITemplate($post);

		$this->assertSame($expectedResult, $result);
	}

}
