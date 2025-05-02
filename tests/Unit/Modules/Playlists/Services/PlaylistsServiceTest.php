<?php

namespace Tests\Unit\Modules\Playlists\Services;

use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use App\Modules\Playlists\Services\AclValidator;
use App\Modules\Playlists\Services\PlaylistMetricsCalculator;
use App\Modules\Playlists\Services\PlaylistsService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PlaylistsServiceTest extends TestCase
{
	private LoggerInterface $loggerMock;
	private readonly PlaylistsRepository $playlistsRepositoryMock;
	private readonly PlaylistMetricsCalculator	$playlistMetricsCalculatorMock;
	private readonly AclValidator $aclValidatorMock;
	private PlaylistsService $service;


	/**
	 * Set up the test environment.
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$this->loggerMock                    = $this->createMock(LoggerInterface::class);
		$this->playlistsRepositoryMock       = $this->createMock(PlaylistsRepository::class);
		$this->aclValidatorMock              = $this->createMock(AclValidator::class);
		$this->playlistMetricsCalculatorMock = $this->createMock(PlaylistMetricsCalculator::class);

		$this->service = new PlaylistsService($this->playlistsRepositoryMock, $this->playlistMetricsCalculatorMock,  $this->aclValidatorMock, $this->loggerMock);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCreateNewSuccessfullyInsertsData(): void
	{
		$postData = ['UID' => 123, 'playlist_mode' => 'private', 'playlist_name' => 'Test Playlist', 'time_limit' => 60];
		$saveData = ['UID' => 123, 'playlist_mode' => 'private', 'playlist_name' => 'Test Playlist', 'time_limit' => 60];

		$this->playlistsRepositoryMock->expects($this->once())->method('insert')
			->with($saveData)
			->willReturn(1);

		$result = $this->service->createNew($postData);

		$this->assertEquals(1, $result);
	}

	#[Group('units')]
	public function testCreateNewSuccessfullyInsertsDataWithoutUID(): void
	{
		$this->service->setUID(567);
		$postData = ['playlist_mode' => 'private', 'playlist_name' => 'Test Playlist', 'multizone' => []];
		$saveData = ['UID' => 567, 'playlist_mode' => 'private', 'playlist_name' => 'Test Playlist', 'multizone' => []];

		$this->playlistsRepositoryMock->expects($this->once())->method('insert')
			->with($saveData)
			->willReturn(1);

		$result = $this->service->createNew($postData);

		$this->assertEquals(1, $result);
	}

	#[Group('units')]
	public function testCreateNewThrowsExceptionOnRepositoryError(): void
	{
		$postData = ['UID' => 123, 'playlist_mode' => 'private', 'playlist_name' => 'Test Playlist'];
		$saveData = ['UID' => 123, 'playlist_mode' => 'private', 'playlist_name' => 'Test Playlist'];

		$this->playlistsRepositoryMock->expects($this->once())->method('insert')
			->with($saveData)
			->willThrowException(new \Exception('Database error'));

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Database error');

		$this->service->createNew($postData);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws Exception
	 */
	#[Group('units')]
	public function testUpdateThrowsExceptionWhenPlaylistNotEditable(): void
	{
		$this->service->setUID(1);
		$postData = ['playlist_id' => 1, 'playlist_name' => 'Updated Playlist'];
		$playlist = ['playlist_id' => 1, 'playlist_name' => 'Original Playlist'];

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstWithUserName')
			->with(1)
			->willReturn($playlist);

		$this->aclValidatorMock->expects($this->once())->method('isPlaylistEditable')
			->with(1, $playlist)
			->willReturn(false);

		$this->expectException(ModuleException::class);

		$this->service->updateSecure($postData);
	}

	#[Group('units')]
	public function testUpdate(): void
	{
		$this->service->setUID(2);
		$postData = ['UID' => 789, 'playlist_id' => 1, 'playlist_name' => 'Updated Playlist'];
		$playlist = ['playlist_id' => 1, 'playlist_name' => 'Original Playlist'];

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstWithUserName')
			->with(1)
			->willReturn($playlist);

		$this->aclValidatorMock->expects($this->once())->method('isPlaylistEditable')
			->with(2, $playlist)
			->willReturn(true);

		$saveData = ['UID' => 789, 'playlist_name' => 'Updated Playlist'];

		$this->playlistsRepositoryMock->expects($this->once())->method('update')
			->with(1, $saveData);

		$this->service->updateSecure($postData);
	}

	#[Group('units')]
	public function testDeleteSucceedsWhenPlaylistIsEditable(): void
	{
		$this->service->setUID(123);
		$playlistId = 1;
		$playlist = ['playlist_id' => $playlistId, 'playlist_name' => 'Deletable Playlist'];

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstWithUserName')
			->with($playlistId)
			->willReturn($playlist);

		$this->aclValidatorMock->expects($this->once())->method('isPlaylistEditable')
			->with(123, $playlist)
			->willReturn(true);

		$this->playlistsRepositoryMock->expects($this->once())->method('delete')
			->with($playlistId)
			->willReturn(1);

		$result = $this->service->delete($playlistId);

		$this->assertEquals(1, $result);
	}

	#[Group('units')]
	public function testDeleteThrowsExceptionWhenPlaylistNotEditable(): void
	{
		$this->service->setUID(456);
		$playlistId = 2;
		$playlist = ['playlist_id' => $playlistId, 'playlist_name' => 'Non-editable Playlist'];

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstWithUserName')
			->with($playlistId)
			->willReturn($playlist);

		$this->aclValidatorMock->expects($this->once())->method('isPlaylistEditable')
			->with(456, $playlist)
			->willReturn(false);

		$this->loggerMock->expects($this->once())->method('error')
			->with('Error delete playlist. Non-editable Playlist is not editable');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Error delete playlist. Non-editable Playlist is not editable');

		$this->service->delete($playlistId);
	}

	#[Group('units')]
	public function testDeleteThrowsExceptionOnRepositoryError(): void
	{
		$this->service->setUID(789);
		$playlistId = 3;
		$playlist = ['playlist_id' => $playlistId, 'playlist_name' => 'Playlist with Error'];

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstWithUserName')
			->with($playlistId)
			->willReturn($playlist);

		$this->aclValidatorMock->expects($this->once())->method('isPlaylistEditable')
			->with(789, $playlist)
			->willReturn(true);

		$this->playlistsRepositoryMock->expects($this->once())->method('delete')
			->with($playlistId)
			->willThrowException(new \Exception('Delete repository error'));

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Delete repository error');

		$this->service->delete($playlistId);
	}

	#[Group('units')]
	public function testLoadPlaylistForMultizoneReturnsDeserializedDataWhenEditable(): void
	{
		$this->service->setUID(1);
		$playlistId = 123;
		$playlist = ['playlist_id' => $playlistId, 'multizone' => serialize(['zone1', 'zone2'])];

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstWithUserName')
			->with($playlistId)
			->willReturn($playlist);

		$this->aclValidatorMock->expects($this->once())->method('isPlaylistEditable')
			->with(1, $playlist)
			->willReturn(true);

		$result = $this->service->loadPlaylistForMultizone($playlistId);

		$this->assertEquals(['zone1', 'zone2'], $result);
	}

	#[Group('units')]
	public function testLoadPlaylistForMultizoneThrowsExceptionWhenPlaylistNotFound(): void
	{
		$this->service->setUID(1);
		$playlistId = 123;

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstWithUserName')
			->with($playlistId)
			->willReturn([]);

		$this->loggerMock->expects($this->once())
			->method('error')
			->with('Error loading playlist. Playlist with Id: 123 not found');

		$result = $this->service->loadPlaylistForMultizone($playlistId);

		$this->assertEquals([], $result);
	}

	#[Group('units')]
	public function testLoadPlaylistForMultizoneReturnsEmptyArrayWhenNotEditable(): void
	{
		$this->service->setUID(1);
		$playlistId = 123;
		$playlist = ['playlist_id' => $playlistId];

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstWithUserName')
			->with($playlistId)
			->willReturn($playlist);

		$this->aclValidatorMock->expects($this->once())->method('isPlaylistEditable')
			->with(1, $playlist)
			->willReturn(false);

		$this->loggerMock->expects($this->once())
			->method('error')
			->with('Error loading playlist: Is not editable');

		$result = $this->service->loadPlaylistForMultizone($playlistId);

		$this->assertEquals([], $result);
	}

	#[Group('units')]
	public function testLoadPlaylistForMultizoneReturnsEmptyArrayOnRepositoryException(): void
	{
		$this->service->setUID(1);
		$playlistId = 123;

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstWithUserName')
			->with($playlistId)
			->willThrowException(new \Exception('Repository error'));

		$this->loggerMock->expects($this->once())
			->method('error')
			->with('Repository error');

		$result = $this->service->loadPlaylistForMultizone($playlistId);

		$this->assertEquals([], $result);
	}

	#[Group('units')]
	public function testLoadPlaylistForMultizoneForNewPlaylists(): void
	{
		$this->service->setUID(1);
		$playlistId = 123;
		$playlist = ['playlist_id' => $playlistId, 'multizone' => ''];

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstWithUserName')
			->with($playlistId)
			->willReturn($playlist);

		$this->aclValidatorMock->expects($this->once())->method('isPlaylistEditable')
			->with(1, $playlist)
			->willReturn(true);

		$result = $this->service->loadPlaylistForMultizone($playlistId);

		$this->assertEmpty($result);

	}


	#[Group('units')]
	public function testLoadNameByIdSuccessfullyReturnsPlaylistData(): void
	{
		$playlistId = 456;
		$playlist = ['playlist_id' => $playlistId, 'playlist_name' => 'Test Playlist'];

		$this->service->setUID(1);
		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstBy')
			->with(['playlist_id' => $playlistId])
			->willReturn($playlist);

		$this->aclValidatorMock->expects($this->once())->method('isPlaylistEditable')
			->with(1, $playlist)
			->willReturn(true);

		$result = $this->service->loadNameById($playlistId);

		$this->assertEquals(['playlist_id' => $playlistId, 'name' => 'Test Playlist'], $result);
	}

	#[Group('units')]
	public function testLoadNameByIdThrowsExceptionWhenPlaylistNotFound(): void
	{
		$playlistId = 789;

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstBy')
			->with(['playlist_id' => $playlistId])
			->willReturn([]);

		$this->loggerMock->expects($this->once())
			->method('error')
			->with('Error loading playlist. Playlist with Id: 789 not found');

		$result = $this->service->loadNameById($playlistId);

		$this->assertEquals([], $result);
	}

	#[Group('units')]
	public function testLoadNameByIdReturnsEmptyArrayOnRepositoryException(): void
	{
		$playlistId = 123;

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstBy')
			->with(['playlist_id' => $playlistId])
			->willThrowException(new \Exception('Repository error'));

		$this->loggerMock->expects($this->once())
			->method('error')
			->with('Repository error');

		$result = $this->service->loadNameById($playlistId);

		$this->assertEquals([], $result);
	}

	#[Group('units')]
	public function testSaveZonesSuccessfullyUpdatesData(): void
	{
		$this->service->setUID(1);
		$playlistId = 123;
		$zones = ['zone1', 'zone2'];
		$playlist = ['playlist_id' => $playlistId, 'multizone' => serialize($zones)];

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstWithUserName')
			->with($playlistId)
			->willReturn($playlist);

		$this->aclValidatorMock->expects($this->once())->method('isPlaylistEditable')
			->with(1, $playlist)
			->willReturn(true);

		$this->playlistsRepositoryMock->expects($this->once())->method('update')
			->with($playlistId, ['multizone' => serialize($zones)])
			->willReturn(1);

		$result = $this->service->saveZones($playlistId, $zones);

		$this->assertEquals(1, $result);
	}

	#[Group('units')]
	public function testSaveZonesThrowsExceptionWhenPlaylistNotFound(): void
	{
		$this->service->setUID(1);
		$playlistId = 123;
		$zones = ['zone1', 'zone2'];

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstWithUserName')
			->with($playlistId)
			->willReturn([]);

		$this->loggerMock->expects($this->once())->method('error')
			->with('Error loading playlist. Playlist with Id: 123 not found');

		$result = $this->service->saveZones($playlistId, $zones);

		$this->assertEquals(0, $result);
	}

	#[Group('units')]
	public function testSaveZonesThrowsExceptionWhenPlaylistNotEditable(): void
	{
		$this->service->setUID(1);
		$playlistId = 123;
		$zones = ['zone1', 'zone2'];
		$playlist = ['playlist_id' => $playlistId];

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstWithUserName')
			->with($playlistId)
			->willReturn($playlist);

		$this->aclValidatorMock->expects($this->once())->method('isPlaylistEditable')
			->with(1, $playlist)
			->willReturn(false);

		$this->loggerMock->expects($this->once())->method('error')
			->with('Error loading playlist: Not editable.');
		$this->service->setUID(1);
		$result = $this->service->saveZones($playlistId, $zones);

		$this->assertEquals(0, $result);
	}

	#[Group('units')]
	public function testSaveZonesReturnsZeroOnRepositoryException(): void
	{
		$this->service->setUID(1);
		$playlistId = 123;
		$zones = ['zone1', 'zone2'];
		$playlist = ['playlist_id' => $playlistId];

		$this->playlistsRepositoryMock->expects($this->once())->method('findFirstWithUserName')
			->with($playlistId)
			->willReturn($playlist);

		$this->aclValidatorMock->expects($this->once())->method('isPlaylistEditable')
			->with(1, $playlist)
			->willReturn(true);

		$this->playlistsRepositoryMock->expects($this->once())->method('update')
			->with($playlistId, ['multizone' => serialize($zones)])
			->willThrowException(new \Exception('Repository error'));

		$this->loggerMock->expects($this->once())->method('error')
			->with('Repository error');

		$result = $this->service->saveZones($playlistId, $zones);

		$this->assertEquals(0, $result);
	}

	#[Group('units')]
	public function testLoadPlaylistForEditReturnsPlaylistDataSuccessfully(): void
	{
		$this->service->setUID(1);
		$playlistId = 999;
		$playlist = ['playlist_id' => $playlistId, 'playlist_name' => 'Sample Playlist'];

		$this->playlistsRepositoryMock->expects($this->once())
			->method('findFirstWithUserName')
			->with($playlistId)
			->willReturn($playlist);

		$this->aclValidatorMock->expects($this->once())
			->method('isPlaylistEditable')
			->with(1, $playlist)
			->willReturn(true);

		$result = $this->service->loadPlaylistForEdit($playlistId);

		$this->assertEquals($playlist, $result);
	}

	#[Group('units')]
	public function testLoadPlaylistForEditThrowsExceptionWhenPlaylistNotFound(): void
	{
		$this->service->setUID(1);
		$playlistId = 888;

		$this->playlistsRepositoryMock->expects($this->once())
			->method('findFirstWithUserName')
			->with($playlistId)
			->willReturn([]);

		$this->loggerMock->expects($this->once())
			->method('error')
			->with('Error loading playlist. Playlist with Id: 888 not found');

		$result = $this->service->loadPlaylistForEdit($playlistId);

		$this->assertEquals([], $result);
	}

	#[Group('units')]
	public function testLoadPlaylistForEditThrowsExceptionWhenPlaylistNotEditable(): void
	{
		$this->service->setUID(2);
		$playlistId = 777;
		$playlist = ['playlist_id' => $playlistId, 'playlist_name' => 'Non-Editable Playlist'];

		$this->playlistsRepositoryMock->expects($this->once())
			->method('findFirstWithUserName')
			->with($playlistId)
			->willReturn($playlist);

		$this->aclValidatorMock->expects($this->once())
			->method('isPlaylistEditable')
			->with(2, $playlist)
			->willReturn(false);

		$this->loggerMock->expects($this->once())
			->method('error')
			->with('Error loading playlist: Not editable.');

		$result = $this->service->loadPlaylistForEdit($playlistId);

		$this->assertEquals([], $result);
	}

	#[Group('units')]
	public function testLoadPlaylistForEditReturnsEmptyArrayOnRepositoryException(): void
	{
		$this->service->setUID(3);
		$playlistId = 666;

		$this->playlistsRepositoryMock->expects($this->once())
			->method('findFirstWithUserName')
			->with($playlistId)
			->willThrowException(new \Exception('Repository error'));

		$this->loggerMock->expects($this->once())
			->method('error')
			->with('Repository error');

		$result = $this->service->loadPlaylistForEdit($playlistId);

		$this->assertEquals([], $result);
	}
}
