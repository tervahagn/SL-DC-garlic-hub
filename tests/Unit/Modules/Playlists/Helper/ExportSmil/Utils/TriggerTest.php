<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);

namespace Tests\Unit\Modules\Playlists\Helper\ExportSmil\Utils;

use App\Modules\Playlists\Helper\ExportSmil\items\Base;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Trigger;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class TriggerTest extends TestCase
{

	#[Group('units')]
	public function testHasTriggersReturnsTrueWhenTriggersExist(): void
	{
		$trigger = new Trigger(['wallclocks' => ['example']]);
		static::assertTrue($trigger->hasTriggers());
	}

	#[Group('units')]
	public function testHasTriggersReturnsFalseWhenTriggersDoNotExist(): void
	{
		$trigger = new Trigger([]);
		static::assertFalse($trigger->hasTriggers());
	}

	#[Group('units')]
	public function testDetermineTriggerWallClocksEndlessYearRepeat(): void
	{
		$wallclocks = [
			[
				'iso_date' => '2023-11-03T10:00:00',
				'repeat_counts' => 0,
				'repeat_years' => 1,
				'repeat_months' => 0,
				'repeat_weeks' => 0,
				'repeat_days' => 0,
				'repeat_hours' => 0,
				'repeat_minutes' => 0,
				'weekday' => 0,
			],
		];
		$triggers = new Trigger(['wallclocks' => $wallclocks]);
		$result = $triggers->determineTrigger();
		static::assertEquals('wallclock(R/2023-11-03T10:00:00/P1Y)', $result);
	}

	#[Group('units')]
	public function testDetermineWallClocksNoRepeat(): void
	{
		$wallclocks = [
			[
				'iso_date' => '2023-11-03T10:00:00',
				'repeat_counts' => -1,
				'repeat_years' => 0,
				'repeat_months' => 0,
				'repeat_weeks' => 1, // error should be ignored
				'repeat_days' => 0,
				'repeat_hours' => 0,
				'repeat_minutes' => 0,
				'weekday' => 0,
			],
		];
		$triggers = new Trigger(['wallclocks' => $wallclocks]);
		$result = $triggers->determineTrigger();
		static::assertEquals('wallclock(2023-11-03T10:00:00)', $result);
	}

	#[Group('units')]
	public function testDetermineWallClocks4WeeksRepeat(): void
	{
		$wallclocks = [
			[
				'iso_date' => '2023-11-03T10:00:00',
				'repeat_counts' => 4,
				'repeat_years' => 0,
				'repeat_months' => 0,
				'repeat_weeks' => 1,
				'repeat_days' => 0,
				'repeat_hours' => 0,
				'repeat_minutes' => 0,
				'weekday' => 0,
			],
		];
		$triggers = new Trigger(['wallclocks' => $wallclocks]);
		$result = $triggers->determineTrigger();
		static::assertEquals('wallclock(R4/2023-11-03T10:00:00/P1W)', $result);
	}

	#[Group('units')]
	public function testDetermineWallClocks2MonthAndDayRepeat(): void
	{
		$wallclocks = [
			[
				'iso_date' => '2023-11-03T10:00:00',
				'repeat_counts' => 2,
				'repeat_years' => 0,
				'repeat_months' => 1,
				'repeat_weeks' => 0,
				'repeat_days' => 1,
				'repeat_hours' => 0,
				'repeat_minutes' => 0,
				'weekday' => 0,
			],
		];
		$triggers = new Trigger(['wallclocks' => $wallclocks]);
		$result = $triggers->determineTrigger();
		static::assertEquals('wallclock(R2/2023-11-03T10:00:00/P1M1D)', $result);
	}

	#[Group('units')]
	public function testDetermineWallClocksEvery90min(): void
	{
		$wallclocks = [
			[
				'iso_date' => '2023-11-03T10:00:00',
				'repeat_counts' => 0,
				'repeat_years' => 0,
				'repeat_months' => 0,
				'repeat_weeks' => 0,
				'repeat_days' => 0,
				'repeat_hours' => 1,
				'repeat_minutes' => 30,
				'weekday' => 0,
			],
		];
		$triggers = new Trigger(['wallclocks' => $wallclocks]);
		$result = $triggers->determineTrigger();
		static::assertEquals('wallclock(R/2023-11-03T10:00:00/PT1H30M)', $result);
	}

	#[Group('units')]
	public function testDetermineWallClocksSomeAbsurd(): void
	{
		$wallclocks = [
			[
				'iso_date' => '2023-11-03T10:00:00',
				'repeat_counts' => 0,
				'repeat_years' => 1,
				'repeat_months' => 1,
				'repeat_weeks' => 1,
				'repeat_days' => 1,
				'repeat_hours' => 1,
				'repeat_minutes' => 1,
				'weekday' => 0,
			],
		];
		$triggers = new Trigger(['wallclocks' => $wallclocks]);
		$result = $triggers->determineTrigger();
		static::assertEquals('wallclock(R/2023-11-03T10:00:00/P1Y1M1W1DT1H1M)', $result);
	}

	#[Group('units')]
	public function testDetermineTriggerFirstWednesdayAfterDatetime(): void
	{
		$wallclocks = [
			[
				'iso_date' => '2025-11-03T10:00:00',
				'repeat_counts' => -1,
				'repeat_years' => 0,
				'repeat_months' => 0,
				'repeat_weeks' => 1,
				'repeat_days' => 0,
				'repeat_hours' => 14,
				'repeat_minutes' => 0,
				'weekday' => 3,
			],
		];
		$triggers = new Trigger(['wallclocks' => $wallclocks]);
		$result = $triggers->determineTrigger();
		static::assertEquals('wallclock(2025-11-03+w3T10:00:00)', $result);
	}

	#[Group('units')]
	public function testDetermineTriggerFirstTuesdayBeforeDatetime(): void
	{
		$wallclocks = [
			[
				'iso_date' => '2025-11-03T10:00:00',
				'repeat_counts' => -1,
				'repeat_years' => 0,
				'repeat_months' => 0,
				'repeat_weeks' => 1,
				'repeat_days' => 0,
				'repeat_hours' => 14,
				'repeat_minutes' => 0,
				'weekday' => -2,
			],
		];
		$triggers = new Trigger(['wallclocks' => $wallclocks]);
		$result = $triggers->determineTrigger();
		static::assertEquals('wallclock(2025-11-03-w2T10:00:00)', $result);
	}

	#[Group('units')]
	public function testDetermineTriggerWallclockIntervalsError(): void
	{
		$wallclocks = [
			[
				'iso_date' => '2025-11-03T10:00:00',
				'repeat_counts' => 15,
				'repeat_years' => 0,
				'repeat_months' => 0,
				'repeat_weeks' => 0,
				'repeat_days' => 0,
				'repeat_hours' => 0,
				'repeat_minutes' => 0,
				'weekday' => 0,
			],
		];
		$triggers = new Trigger(['wallclocks' => $wallclocks]);
		$result = $triggers->determineTrigger();
		static::assertEquals('wallclock(2025-11-03T10:00:00)', $result);
	}



	#[Group('units')]
	public function testDetermineTriggerReturnsExpectedStringForAccessKeys(): void
	{
		$accessKeys = [['accesskey' => 'A']];
		$triggers = new Trigger(['accesskeys' => $accessKeys]);
		$result = $triggers->determineTrigger();
		static::assertEquals('accesskey(A)', $result);
	}

	#[Group('units')]
	public function testDetermineTriggerReturnsExpectedStringForTouches(): void
	{
		$touches = [['touch_item_id' => '123']];
		$triggers = new Trigger(['touches' => $touches]);
		$result = $triggers->determineTrigger();
		static::assertEquals(Base::ID_PREFIX.'123.activateEvent', $result);
	}

	#[Group('units')]
	public function testDetermineTriggerReturnsExpectedStringForNotifies(): void
	{
		$notifies = [['notify' => 'event']];
		$triggers = new Trigger(['notifies' => $notifies]);
		$result = $triggers->determineTrigger();
		static::assertEquals('notify(event)', $result);
	}

	#[Group('units')]
	public function testDetermineTriggerReturnsEmptyStringWhenNoTriggers(): void
	{
		$triggers = new Trigger([]);
		$result = $triggers->determineTrigger();
		static::assertEquals('', $result);
	}
}
