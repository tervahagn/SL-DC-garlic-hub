<?php

namespace Tests\Unit\Modules\Playlists\Services\InsertItems;

use App\Modules\Mediapool\Services\MediaService;
use App\Modules\Playlists\Repositories\ItemsRepository;
use App\Modules\Playlists\Services\InsertItems\InsertItemFactory;
use App\Modules\Playlists\Services\InsertItems\Media;
use App\Modules\Playlists\Services\InsertItems\Playlist;
use App\Modules\Playlists\Services\PlaylistMetricsCalculator;
use App\Modules\Playlists\Services\PlaylistsService;
use App\Modules\Playlists\Services\WidgetsService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InsertItemFactoryTest extends TestCase
{
	private InsertItemFactory $factory;


	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$mediaServiceMock = $this->createMock(MediaService::class);
		$itemsRepositoryMock = $this->createMock(ItemsRepository::class);
		$playlistsServiceMock = $this->createMock(PlaylistsService::class);
		$playlistMetricsCalculatorMock = $this->createMock(PlaylistMetricsCalculator::class);
		$widgetsServiceMock = $this->createMock(WidgetsService::class);
		$loggerMock = $this->createMock(LoggerInterface::class);

		$this->factory = new InsertItemFactory(
			$mediaServiceMock,
			$itemsRepositoryMock,
			$playlistsServiceMock,
			$playlistMetricsCalculatorMock,
			$widgetsServiceMock,
			$loggerMock
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
