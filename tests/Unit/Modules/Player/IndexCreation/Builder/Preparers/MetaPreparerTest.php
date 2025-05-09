<?php

namespace Tests\Unit\Modules\Player\IndexCreation\Builder\Preparers;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\IndexCreation\Builder\Preparers\MetaPreparer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class MetaPreparerTest extends TestCase
{
	private readonly PlayerEntity $playerEntityMock;
	private MetaPreparer $preparer;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->playerEntityMock = $this->createMock(PlayerEntity::class);
		$this->preparer   = new MetaPreparer($this->playerEntityMock);
	}

	#[Group('units')]
	public function testPrepareReturnsExpectedMetaArray(): void
	{
		$this->playerEntityMock->method('getPlayerName')->willReturn('Test Player');
		$this->playerEntityMock->method('getPlaylistName')->willReturn('Test Playlist');
		$this->playerEntityMock->method('getRefresh')->willReturn(120);

		$result = $this->preparer->prepare();

		$this->assertEquals(
			[['TITLE' => 'Test Player - Test Playlist', 'REFRESH_TIME' => 120]],
			$result
		);
	}
}
