<?php

namespace Tests\Unit\Framework\Utils\Datatable;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Utils\Datatable\TimeUnitsCalculator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TimeUnitsCalculatorTest extends TestCase
{
	private Translator&MockObject $translatorMock;
	private TimeUnitsCalculator $timeUnitsCalculator;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->translatorMock = $this->createMock(Translator::class);

		$this->timeUnitsCalculator = new TimeUnitsCalculator($this->translatorMock);
	}

	#[Group('units')]
	public function testCalculateLastAccess()
	{

		$this->timeUnitsCalculator->calculateLastAccess(
			new \DateTime('2025-01-01 00:00:11'),
			new \DateTime('2025-01-01 00:00:00')
		);

		$this->assertSame(11, $this->timeUnitsCalculator->getLastAccessTimeStamp());
	}

	#[Group('units')]
	public function testCalculateLastAccessNegative()
	{
		$this->expectException(FrameworkException::class);
		$this->expectExceptionMessage('Negative time difference.');

		$this->timeUnitsCalculator->calculateLastAccess(
			new \DateTime('2025-01-01 00:00:00'),
			new \DateTime('2025-01-01 00:00:10')
		);
	}

	#[Group('units')]
	public function testPrintDistanceSecond()
	{
		$this->timeUnitsCalculator->calculateLastAccess(
			new \DateTime('2025-01-01 00:00:01'),
			new \DateTime('2025-01-01 00:00:00')
		);

		$this->translatorMock->expects($this->once())->method('translateArrayWithPlural')
			->with('seconds', 'time_unit_ago', 'main', 1, [])
			->willReturn('1 second ago');
		$this->assertSame('1 second ago', $this->timeUnitsCalculator->printDistance());
	}

	#[Group('units')]
	public function testPrintDistanceMinutes()
	{
		$this->timeUnitsCalculator->calculateLastAccess(
			new \DateTime('2025-01-01 00:10:10'),
			new \DateTime('2025-01-01 00:00:00')
		);

		$this->translatorMock->expects($this->once())->method('translateArrayWithPlural')
			->with('minutes', 'time_unit_ago', 'main', 10, [])
			->willReturn('10 minutes ago');
		$this->assertSame('10 minutes ago', $this->timeUnitsCalculator->printDistance());
	}

	#[Group('units')]
	public function testPrintDistanceHours()
	{
		$this->timeUnitsCalculator->calculateLastAccess(
			new \DateTime('2025-01-01 01:10:10'),
			new \DateTime('2025-01-01 00:00:00')
		);

		$this->translatorMock->expects($this->once())->method('translateArrayWithPlural')
			->with('hours', 'time_unit_ago', 'main', 1, [])
			->willReturn('1 hour ago');
		$this->assertSame('1 hour ago', $this->timeUnitsCalculator->printDistance());
	}

	#[Group('units')]
	public function testPrintDistanceDays()
	{
		$this->timeUnitsCalculator->calculateLastAccess(
			new \DateTime('2025-01-03 01:10:10'),
			new \DateTime('2025-01-01 00:00:00')
		);

		$this->translatorMock->expects($this->once())->method('translateArrayWithPlural')
			->with('days', 'time_unit_ago', 'main', 2, [])
			->willReturn('2 days ago');
		$this->assertSame('2 days ago', $this->timeUnitsCalculator->printDistance());
	}

	#[Group('units')]
	public function testPrintDistanceMonths()
	{
		$this->timeUnitsCalculator->calculateLastAccess(
			new \DateTime('2025-05-03 01:10:10'),
			new \DateTime('2025-01-01 00:00:00')
		);

		$this->translatorMock->expects($this->once())->method('translateArrayWithPlural')
			->with('months', 'time_unit_ago', 'main', 4, [])
			->willReturn('4 months ago');
		$this->assertSame('4 months ago', $this->timeUnitsCalculator->printDistance());
	}

	#[Group('units')]
	public function testPrintDistanceYears()
	{
		$this->timeUnitsCalculator->calculateLastAccess(
			new \DateTime('2030-01-02 01:10:10'),
			new \DateTime('2025-01-01 00:00:00')
		);

		$this->translatorMock->expects($this->once())->method('translateArrayWithPlural')
			->with('years', 'time_unit_ago', 'main', 5, [])
			->willReturn('5 years ago');
		$this->assertSame('5 years ago', $this->timeUnitsCalculator->printDistance());
	}

}
