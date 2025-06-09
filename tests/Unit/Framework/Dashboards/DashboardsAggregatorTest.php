<?php

namespace Tests\Unit\Framework\Dashboards;

use App\Framework\Dashboards\DashboardInterface;
use App\Framework\Dashboards\DashboardsAggregator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class DashboardsAggregatorTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testRegisterAndRenderDashboardsContents(): void
	{
		$dashboardMock = $this->createMock(DashboardInterface::class);
		$dashboardMock->method('getId')->willReturn('test');
		$dashboardMock->method('getTitle')->willReturn('Test Title');
		$dashboardMock->method('renderContent')->willReturn('Test Content');

		$aggregator = new DashboardsAggregator();
		$aggregator->registerDashboard($dashboardMock);

		$result = $aggregator->renderDashboardsContents();

		$this->assertCount(1, $result);
		$this->assertSame('Test Title', $result[0]['LANG_DASHBOARD_TITLE']);
		$this->assertSame('Test Content', $result[0]['DASHBOARD_CONTENT']);
	}

}
