<?php

namespace Tests\Unit\Modules\Playlists\Collector\Builder;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Playlists\Collector\Builder\BuildHelper;
use App\Modules\Playlists\Collector\Builder\MultizonePlaylistBuilder;
use App\Modules\Playlists\Collector\Builder\PlaylistBuilderFactory;
use App\Modules\Playlists\Collector\Builder\StandardPlaylistBuilder;
use App\Modules\Playlists\Collector\SimplePlaylistStructureFactory;
use App\Modules\Playlists\Helper\PlaylistMode;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class PlaylistBuilderFactoryTest extends TestCase
{
	private PlaylistBuilderFactory $factory;
	private PlayerEntity $playerEntityMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$buildHelperMock = $this->createMock(BuildHelper::class);
		$simplePlaylistStructureFactoryMock = $this->createMock(SimplePlaylistStructureFactory::class);

		$this->playerEntityMock = $this->createMock(PlayerEntity::class);


		$this->factory = new PlaylistBuilderFactory(
			$buildHelperMock,
			$simplePlaylistStructureFactoryMock
		);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCreateBuilderReturnsMultizonePlaylistBuilder(): void
	{
		$this->playerEntityMock->method('getPlaylistMode')->willReturn(PlaylistMode::MULTIZONE->value);

		$result = $this->factory->createBuilder($this->playerEntityMock);

		$this->assertInstanceOf(MultiZonePlaylistBuilder::class, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCreateBuilderReturnsStandardPlaylistBuilder(): void
	{
		$this->playerEntityMock->method('getPlaylistMode')->willReturn(PlaylistMode::MASTER->value);

		$result = $this->factory->createBuilder($this->playerEntityMock);

		$this->assertInstanceOf(StandardPlaylistBuilder::class, $result);
	}
}
