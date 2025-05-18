<?php

namespace Tests\Unit\Modules\Playlists\Collector\Builder;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Playlists\Collector\Builder\BuildHelper;
use App\Modules\Playlists\Collector\Builder\MultizonePlaylistBuilder;
use App\Modules\Playlists\Collector\Contracts\PlaylistStructureInterface;
use App\Modules\Playlists\Collector\SimplePlaylistStructureFactory;
use App\Modules\Playlists\Helper\ExportSmil\items\Base;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class MultizonePlaylistBuilderTest extends TestCase
{
	private PlayerEntity $playerEntityMock;
	private BuildHelper $buildHelperMock;
	private SimplePlaylistStructureFactory $simplePlaylistStructureFactoryMock;
	private MultizonePlaylistBuilder $builder;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->playerEntityMock = $this->createMock(PlayerEntity::class);
		$this->buildHelperMock = $this->createMock(BuildHelper::class);
		$this->simplePlaylistStructureFactoryMock = $this->createMock(SimplePlaylistStructureFactory::class);

		$this->builder = new MultizonePlaylistBuilder(
			$this->playerEntityMock,
			$this->buildHelperMock,
			$this->simplePlaylistStructureFactoryMock
		);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testBuildPlaylistWithValidZones(): void
	{
		$zones = ['zones' => [1 => ['zone_playlist_id' => 101]]];

		$this->playerEntityMock->method('getZones')->willReturn($zones);

		$this->buildHelperMock->method('collectItems')
			->with(101)
			->willReturn('region="screen" item 101')
		;

		$this->buildHelperMock->method('collectPrefetches')
			->with(101)
			->willReturn('prefetch 101')
		;

		$this->buildHelperMock->method('collectExclusives')
			->with(101)
			->willReturn('region="screen" exclusive 101')
		;

		$playlistStructureMock = $this->createMock(PlaylistStructureInterface::class);

		$items = Base::TABSTOPS_TAG . '<seq id="media1" repeatCount="indefinite">' . "\n" .
			'region="screen1" item 101'.
			Base::TABSTOPS_TAG . '</seq>' . "\n";


		$this->simplePlaylistStructureFactoryMock->method('create')
			->with($items, 'prefetch 101'. "\n", 'region="screen1" exclusive 101')
			->willReturn($playlistStructureMock);

		$result = $this->builder->buildPlaylist();

		$this->assertSame($playlistStructureMock, $result);
	}


}
