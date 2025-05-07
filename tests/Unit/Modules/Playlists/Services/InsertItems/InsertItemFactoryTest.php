<?php

namespace Tests\Unit\Modules\Playlists\Services\InsertItems;

use App\Modules\Mediapool\Services\MediaService;
use App\Modules\Playlists\Repositories\ItemsRepository;
use App\Modules\Playlists\Services\InsertItems\InsertItemFactory;
use App\Modules\Playlists\Services\InsertItems\Media;
use App\Modules\Playlists\Services\InsertItems\Playlist;
use App\Modules\Playlists\Services\PlaylistMetricsCalculator;
use App\Modules\Playlists\Services\PlaylistsService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InsertItemFactoryTest extends TestCase
{
	private readonly MediaService $mediaServiceMock;
	private readonly ItemsRepository $itemsRepositoryMock;
	private readonly PlaylistsService $playlistsServiceMock;
	private readonly PlaylistMetricsCalculator $playlistMetricsCalculatorMock;
	private readonly LoggerInterface $loggerMock;
	private InsertItemFactory $factory;

	protected function setUp(): void
	{
		$this->mediaServiceMock = $this->createMock(MediaService::class);
		$this->itemsRepositoryMock = $this->createMock(ItemsRepository::class);
		$this->playlistsServiceMock = $this->createMock(PlaylistsService::class);
		$this->playlistMetricsCalculatorMock = $this->createMock(PlaylistMetricsCalculator::class);
		$this->loggerMock = $this->createMock(LoggerInterface::class);

		$this->factory = new InsertItemFactory(
			$this->mediaServiceMock,
			$this->itemsRepositoryMock,
			$this->playlistsServiceMock,
			$this->playlistMetricsCalculatorMock,
			$this->loggerMock
		);
	}

	#[Group('units')]
	public function testCreateReturnsMediaInstanceForMediapoolSource(): void
	{
		$result = $this->factory->create('mediapool');

		$this->assertInstanceOf(Media::class, $result);
	}

	#[Group('units')]
	public function testCreateReturnsPlaylistInstanceForPlaylistSource(): void
	{
		$result = $this->factory->create('playlist');

		$this->assertInstanceOf(Playlist::class, $result);
	}

	#[Group('units')]
	public function testCreateReturnsNullForInvalidSource(): void
	{
		$result = $this->factory->create('invalid_source');

		$this->assertNull($result);
	}


}
