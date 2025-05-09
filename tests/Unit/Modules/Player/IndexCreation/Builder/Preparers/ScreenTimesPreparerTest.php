<?php

namespace Tests\Unit\Modules\Player\IndexCreation\Builder\Preparers;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\Enums\PlayerModel;
use App\Modules\Player\IndexCreation\Builder\Preparers\ScreenTimesPreparer;
use DateInterval;
use DateTime;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ScreenTimesPreparerTest extends TestCase
{
	private readonly PlayerEntity $playerEntityMock;
	private ScreenTimesPreparer $preparer;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->playerEntityMock = $this->createMock(PlayerEntity::class);
		$this->preparer         = new ScreenTimesPreparer($this->playerEntityMock);
	}

	#[Group('units')]
	public function testPrepareReturnsEmpty(): void
	{
		$this->playerEntityMock
			->method('getModel')
			->willReturn(PlayerModel::COMPATIBLE);

		$result = $this->preparer->prepare();

		$this->assertEmpty($result);
	}

	#[Group('units')]
	public function testPrepareReturnsScreenTimesForValidScreenTimes(): void
	{
		$screenTimes = [
			[
				'day' => 0,
				'periods' => [
					['start' => '08:00', 'end' => '12:00'],
					['start' => '14:00', 'end' => '18:00']
				]
			],
		];

		$datetime = new DateTime();
		$datetime->sub(new DateInterval('P1M'));
		$date = $datetime->format('Y-m-d');
		$expectedResult = [[
			'BEGIN_WALLCLOCKS' => 'wallclock(R/'.$date.'+w1T12:00:00/P1W);wallclock(R/'.$date.'+w1T18:00:00/P1W)',
			'END_WALLCLOCKS' => 'wallclock(R/'.$date.'+w1T08:00:00/P1W);wallclock(R/'.$date.'+w1T14:00:00/P1W)',
		]];

		$this->playerEntityMock->method('getModel')->willReturn(PlayerModel::GARLIC);
		$this->playerEntityMock->method('getScreenTimes')->willReturn($screenTimes);

		$result = $this->preparer->prepare();

		$this->assertEquals($expectedResult, $result);
	}

	#[Group('units')]
	public function testPrepareReturnsEmptyArrayWhenNoValidScreenTimesExist(): void
	{
		$this->playerEntityMock
			->method('getModel')
			->willReturn(PlayerModel::GARLIC);

		$this->playerEntityMock
			->method('getScreenTimes')
			->willReturn([]);

		$result = $this->preparer->prepare();

		$this->assertEmpty($result);
	}


}
