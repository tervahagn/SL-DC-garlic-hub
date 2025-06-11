<?php

namespace Tests\Unit\Modules\Player\IndexCreation\Builder\Preparers;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\IndexCreation\Builder\Preparers\PlaylistPreparer;
use App\Modules\Playlists\Collector\Contracts\PlaylistStructureInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PlaylistPreparerTest extends TestCase
{
	private readonly PlayerEntity&MockObject $playerEntityMock;
	private PlaylistStructureInterface&MockObject $playlistStructureMock;
	private PlaylistPreparer $preparer;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->playerEntityMock = $this->createMock(PlayerEntity::class);
		$this->playlistStructureMock = $this->createMock(PlaylistStructureInterface::class);
		$this->preparer = new PlaylistPreparer($this->playerEntityMock);
	}

	#[Group('units')]
	public function testPrepareWithSimpleStructure(): void
	{
		$this->preparer->setIsSimple(true)
			->setPlaylistStructure($this->playlistStructureMock);

		$this->playlistStructureMock
			->expects($this->once())
			->method('getItems')
			->willReturn('item1,item2');

		$result = $this->preparer->prepare();

		$this->assertEquals(
			[['INSERT_ELEMENTS' => 'item1,item2']],
			$result
		);
	}

	#[Group('units')]
	public function testPrepareWithComplexStructure(): void
	{
		$this->preparer->setIsSimple(false)
			->setPlaylistStructure($this->playlistStructureMock);

		$this->playlistStructureMock->method('getExclusive')
			->willReturn('exclusiveData');
		$this->playlistStructureMock->method('getItems')
			->willReturn('item1,item2');
		$this->playlistStructureMock->method('getPrefetch')
			->willReturn('prefetchData');

		$this->playerEntityMock->expects($this->once())->method('getDuration')
			->willReturn(500);
		$this->playerEntityMock->expects($this->exactly(2))->method('getRefresh')
			->willReturn(800);

		$result = $this->preparer->prepare();

		$this->assertEquals(
			[[
				'INSERT_PRIORITY_CLASSES' => 'exclusiveData',
				'INSERT_ELEMENTS' => 'item1,item2',
				'INSERT_PREFETCH_ELEMENTS' => 'prefetchData',
				'PREFETCH_REFRESH_TIME' => 900,
			]],
			$result
		);
	}

	#[Group('units')]
	public function testCalculateDurationWhenRefreshIsGreater(): void
	{
		$this->playerEntityMock
			->method('getDuration')
			->willReturn(400);
		$this->playerEntityMock
			->method('getRefresh')
			->willReturn(800);

		$result = $this->preparer->calculatePrefetchDuration();
		$this->assertEquals(900, $result);
	}

	#[Group('units')]
	public function testCalculateDurationWhenDurationIsGreater(): void
	{
		$this->playerEntityMock
			->method('getDuration')
			->willReturn(1200);
		$this->playerEntityMock
			->method('getRefresh')
			->willReturn(800);

		$result = $this->preparer->calculatePrefetchDuration();
		$this->assertEquals(2402, $result);
	}

	#[Group('units')]
	public function testCalculateDurationWhenMinimumValueApplies(): void
	{
		$this->playerEntityMock
			->method('getDuration')
			->willReturn(400);
		$this->playerEntityMock
			->method('getRefresh')
			->willReturn(300);

		$result = $this->preparer->calculatePrefetchDuration();
		$this->assertEquals(900, $result);
	}

	#[Group('units')]
	public function testCalculateDurationWhenDoubleValueApplies(): void
	{
		$this->playerEntityMock
			->method('getDuration')
			->willReturn(950);
		$this->playerEntityMock
			->method('getRefresh')
			->willReturn(1000);

		$result = $this->preparer->calculatePrefetchDuration();
		$this->assertEquals(2002, $result);
	}

}
