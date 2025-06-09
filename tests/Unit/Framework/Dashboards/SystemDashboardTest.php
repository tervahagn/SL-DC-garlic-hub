<?php

namespace Tests\Unit\Framework\Dashboards;

use App\Framework\Core\SystemStats;
use App\Framework\Core\Translate\Translator;
use App\Framework\Dashboards\SystemDashboard;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class SystemDashboardTest extends TestCase
{
	private SystemStats $systemStatsMock;
	private Translator $translatorMock;
	private SystemDashboard $systemDashboard;

	protected function setUp(): void
	{
		$this->systemStatsMock = $this->createMock(SystemStats::class);
		$this->translatorMock = $this->createMock(Translator::class);

		$this->systemDashboard = new SystemDashboard($this->systemStatsMock, $this->translatorMock);
	}

	#[Group('units')]
	public function testGetIdReturnsCorrectValue(): void
	{
		$result = $this->systemDashboard->getId();

		$this->assertSame('system', $result);
	}

	#[Group('units')]
	public function testGetTitleReturnsCorrectTranslatedValue(): void
	{
		$this->translatorMock
			->method('translate')
			->with('system_dashboard', 'main')
			->willReturn('System Dashboard');

		$result = $this->systemDashboard->getTitle();

		$this->assertSame('System Dashboard', $result);
	}

	#[Group('units')]
	public function testRenderContentReturnsCorrectHtml(): void
	{
		$this->systemStatsMock
			->expects($this->once())
			->method('determineSystemStats');

		$this->systemStatsMock
			->method('getLoadData')
			->willReturn(['1.23', '4.56', '7.89']);

		$this->systemStatsMock
			->method('getRamStats')
			->willReturn(['used' => '2GB', 'total' => '8GB']);

		$this->systemStatsMock
			->method('getDiscInfo')
			->willReturn(['used' => '100GB', 'size' => '500GB', 'percent' => '20%']);

		$this->translatorMock
			->method('translate')
			->willReturnMap([
				['memory_used', 'main', [], '2GB of 8GB'],
				['disc_used', 'main', [], '%s of %s (%s)'],
				['system_load', 'main', [], 'System Load'],
				['memory_usage', 'main', [], 'Memory Usage'],
				['disc_usage', 'main', [], 'Disc Usage'],
			]);

		$result = $this->systemDashboard->renderContent();

		$expected = '<ul>
	<li><strong>System Load:</strong><span>1.23 | 4.56 | 7.89</span></li>
	<li><strong>Memory Usage:</strong><span>2GB of 8GB</span></li>
	<li><strong>Disc Usage:</strong><span>100GB of 500GB (20%)</span></li>
</ul>';

		$this->assertSame($expected, $result);
	}
}
