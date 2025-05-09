<?php

namespace Tests\Unit\Modules\Player\IndexCreation\Builder\Preparers;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\IndexCreation\Builder\Preparers\SubscriptionPreparer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class SubscriptionPreparerTest extends TestCase
{
	private readonly PlayerEntity $playerEntityMock;
	private SubscriptionPreparer $preparer;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->playerEntityMock = $this->createMock(PlayerEntity::class);
		$this->preparer         = new SubscriptionPreparer($this->playerEntityMock);
	}

	#[Group('units')]
	public function testPrepareAddsTaskScheduleWhenCommandsExist(): void
	{
		$this->playerEntityMock
			->method('getCommands')
			->willReturn(['command1']);
		$this->playerEntityMock
			->method('getIndexPath')
			->willReturn('/path/to/index');

		$result = $this->preparer->prepare();

		$this->assertCount(1, $result);
		$this->assertEquals('TaskSchedule', $result[0]['SUBSCRIPTION_TYPE']);
	}

	#[Group('units')]
	public function testPrepareAddsReportSubscriptionsWhenReportsExist(): void
	{
		$this->playerEntityMock
			->method('getCommands')
			->willReturn([]);
		$this->playerEntityMock
			->method('getReports')
			->willReturn([
				'inventory' => true,
				'play' => true,
				'events' => true,
				'configuration' => true,
				'executions' => true
			]);
		$this->playerEntityMock
			->method('getReportServer')
			->willReturn('http://report.server');
		$this->playerEntityMock
			->method('getUuid')
			->willReturn('uuid_value');

		$result = $this->preparer->prepare();

		$this->assertCount(5, $result);
		$this->assertEquals('InventoryReport', $result[0]['SUBSCRIPTION_TYPE']);
		$this->assertEquals('PlaylogCollection', $result[1]['SUBSCRIPTION_TYPE']);
		$this->assertEquals('EventlogCollection', $result[2]['SUBSCRIPTION_TYPE']);
		$this->assertEquals('SystemReport', $result[3]['SUBSCRIPTION_TYPE']);
		$this->assertEquals('TaskExecutionReport', $result[4]['SUBSCRIPTION_TYPE']);
	}

	#[Group('units')]
	public function testPrepareReturnsEmptyArrayWhenNoCommandsOrReports(): void
	{
		$this->playerEntityMock
			->method('getCommands')
			->willReturn([]);
		$this->playerEntityMock
			->method('getReports')
			->willReturn([]);

		$result = $this->preparer->prepare();

		$this->assertEmpty($result);
	}
}
