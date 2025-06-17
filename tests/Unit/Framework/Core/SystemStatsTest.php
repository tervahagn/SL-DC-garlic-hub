<?php

namespace Tests\Unit\Framework\Core;

use App\Framework\Core\ShellExecutor;
use App\Framework\Core\SystemStats;
use App\Framework\Exceptions\CoreException;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SystemStatsTest extends TestCase
{
	use PHPMock;
	private ShellExecutor&MockObject $shellExecutorMock;
	private SystemStats $systemStats;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->shellExecutorMock = $this->createMock(ShellExecutor::class);
		$this->systemStats = new SystemStats($this->shellExecutorMock);
	}

	#[Group('units')]
	public function testISLinux(): void
	{
		$this->systemStats->setIsLinux(true);
		$this->assertTrue($this->systemStats->isLinux());
	}

	#[RunInSeparateProcess] #[Group('units')]
	public function testDetermineSystemLoadSetsLoadDataCorrectly(): void
	{
		$getLoadAvg = $this->getFunctionMock('App\Framework\Core', 'sys_getloadavg');
		$getLoadAvg->expects($this->once())->willReturn([1.23, 0.56, 2.03]);

		$expectedFormatted = [
			'1_min' => '1.23',
			'5_min' => '0.56',
			'15_min' => '2.03',
		];

		$this->systemStats->determineSystemLoad();

		$this->assertSame($expectedFormatted, $this->systemStats->getLoadData());
	}

	#[RunInSeparateProcess] #[Group('units')]
	public function testDetermineSystemLoadReturnWrong(): void
	{
		$getLoadAvg = $this->getFunctionMock('App\Framework\Core', 'sys_getloadavg');
		$getLoadAvg->expects($this->once())->willReturn([1.23, 0.56]);

		$expectedFormatted = [
			'1_min' => '',
			'5_min' => '',
			'15_min' => '',
		];

		$this->systemStats->determineSystemLoad();

		$this->assertSame($expectedFormatted, $this->systemStats->getLoadData());
	}


	#[RunInSeparateProcess] #[Group('units')]
	public function testDetermineSystemLoadFalse(): void
	{
		$getLoadAvg = $this->getFunctionMock('App\Framework\Core', 'sys_getloadavg');
		$getLoadAvg->expects($this->once())->willReturn(false);

		$expectedFormatted = [
			'1_min' => '',
			'5_min' => '',
			'15_min' => '',
		];

		$this->systemStats->determineSystemLoad();

		$this->assertSame($expectedFormatted, $this->systemStats->getLoadData());
	}


	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testDetermineRamStatsSetsRamStatsCorrectly(): void
	{
		$mockOutput = "              total        used        free\nMem:          8000        6000        2000";
		$this->shellExecutorMock->method('setCommand')
			->with('free -m')
			->willReturnSelf();

		$this->shellExecutorMock->method('executeSimple')
			->willReturn($mockOutput);

		$this->systemStats->determineRamStats();

		$expected = ['total' => 8000, 'used' => 6000, 'free' => 2000];
		$this->assertSame($expected, $this->systemStats->getRamStats());
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testDetermineRamStatsHandlesInvalidOutput(): void
	{
		$mockOutput = "invalid output";

		$this->shellExecutorMock->method('setCommand')
			->with('free -m')
			->willReturnSelf();

		$this->shellExecutorMock->method('executeSimple')
			->willReturn($mockOutput);

		$this->systemStats->determineRamStats();

		$expected = ['total' => 0, 'used' => 0, 'free' => 0];
		$this->assertSame($expected, $this->systemStats->getRamStats());
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testDetermineDiskUsageSetsDiskStatsCorrectly(): void
	{
		$mockOutput = "Filesystem      Size  Used Avail Use% Mounted on\n"
			. "total           100G   75G   25G  75% -";

		$this->shellExecutorMock->method('setCommand')
			->with('df -h --total')
			->willReturnSelf();

		$this->shellExecutorMock->method('executeSimple')
			->willReturn($mockOutput);

		$this->systemStats->determineDiskUsage();

		$expected = [
			'size' => '100G',
			'used' => '75G',
			'available' => '25G',
			'percent' => '75%',
		];
		$this->assertSame($expected, $this->systemStats->getDiscInfo());
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testDetermineDiskUsageHandlesInvalidOutput(): void
	{
		$mockOutput = "Invalid data format";

		$this->shellExecutorMock->method('setCommand')
			->with('df -h --total')
			->willReturnSelf();

		$this->shellExecutorMock->method('executeSimple')
			->willReturn($mockOutput);

		$this->systemStats->determineDiskUsage();

		$expected = [
			'size' => '',
			'used' => '',
			'available' => '',
			'percent' => '',
		];
		$this->assertSame($expected, $this->systemStats->getDiscInfo());
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testDetermineDiskUsageHandlesInvalidPregsOutput(): void
	{
		$mockOutput = "Filesystem      Size  Used Avail Use% Mounted on\n"
			. "total           100G   75G";


		$this->shellExecutorMock->method('setCommand')
			->with('df -h --total')
			->willReturnSelf();

		$this->shellExecutorMock->method('executeSimple')
			->willReturn($mockOutput);

		$this->systemStats->determineDiskUsage();

		$expected = [
			'size' => '',
			'used' => '',
			'available' => '',
			'percent' => '',
		];
		$this->assertSame($expected, $this->systemStats->getDiscInfo());
	}


	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testDetermineSystemStatsHandlesNonLinuxSystems(): void
	{
		$this->systemStats->setIsLinux(false);

		$this->shellExecutorMock->expects($this->never())->method('setCommand');
		$this->systemStats->determineSystemStats();

		$expected = ['total' => 0, 'used' => 0, 'free' => 0];
		$this->assertSame($expected, $this->systemStats->getRamStats());
		$expected = [
			'size' => '',
			'used' => '',
			'available' => '',
			'percent' => '',
		];
		$this->assertSame($expected, $this->systemStats->getDiscInfo());
		$expected = [
			'1_min' => '',
			'5_min' => '',
			'15_min' => '',
		];
		$this->assertSame($expected, $this->systemStats->getLoadData());
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testDetermineSystemStatsSetsAllStatsCorrectly(): void
	{
		$this->systemStats->setIsLinux(true);

		$this->shellExecutorMock->expects($this->exactly(2))->method('setCommand')
			->willReturnSelf();

		$this->shellExecutorMock->expects($this->exactly(2))->method('executeSimple');

		$this->systemStats->determineSystemStats();

	}
}
