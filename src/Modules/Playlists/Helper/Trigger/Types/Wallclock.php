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


namespace App\Modules\Playlists\Helper\Trigger\Types;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class Wallclock
{
	private string $moduleName = 'playlists';
	/** @var list<array<string,string>>|array<empty,empty>  */
	private array $wallclocks = [];

	public function __construct(private readonly Translator $translator){}

	/**
	 * @return list<array<string,string>>|array<empty,empty>
	 */
	public function getWallclocks(): array
	{
		return $this->wallclocks;
	}

	/**
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 */
	public function generateEmptyWallclock(): static
	{
		$this->wallclocks[] = array_merge(
			['WALLCLOCK_COUNT' => '[WALLCLOCK_COUNT]'],
			$this->generateRemoveWallclock(),
			$this->generatePrefixDateTime(),
			$this->generateDateTime(),
			$this->generateRepeats(),
			$this->generateMinutes(),
			$this->generateHours(),
			$this->generateDays(),
			$this->generateWeeks(),
			$this->generateMonths(),
			$this->generateYears()

		);

		return $this;
	}

	/**
	 * @param list<array{
	 *     weekday: int|string,
	 *     iso_date: string,
	 *     repeat_counts: int|string,
	 *     repeat_minutes: int|string,
	 *     repeat_hours: int|string,
	 *     repeat_days: int|string,
	 *     repeat_weeks: int|string,
	 *     repeat_months: int|string,
	 *     repeat_years: int|string
	 *     }>|array<empty,empty> $wallclocksData
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function generateWallclocks(array $wallclocksData): static
	{
		/**
		 * @var array{
		 *      weekday: int|string,
		 *      iso_date: string,
		 *      repeat_counts: int|string,
		 *      repeat_minutes: int|string,
		 *      repeat_hours: int|string,
		 *      repeat_days: int|string,
		 *      repeat_weeks: int|string,
		 *      repeat_months: int|string,
		 *      repeat_years: int|string
		 *      } $wallclockData
		 */
		foreach ($wallclocksData as $key => $wallclockData)
		{
			$this->wallclocks[] = array_merge(
				['WALLCLOCK_COUNT' => $key],
				$this->generateRemoveWallclock(),
				$this->generatePrefixDateTime((int) $wallclockData['weekday']),
				$this->generateDateTime($wallclockData['iso_date']),
				$this->generateRepeats((int) $wallclockData['repeat_counts']),
				$this->generateMinutes((int) $wallclockData['repeat_minutes']),
				$this->generateHours((int) $wallclockData['repeat_hours']),
				$this->generateDays((int) $wallclockData['repeat_days']),
				$this->generateWeeks((int) $wallclockData['repeat_weeks']),
				$this->generateMonths((int) $wallclockData['repeat_months']),
				$this->generateYears((int) $wallclockData['repeat_years'])
			);

		}

		return $this;
	}

	/**
	 * @param int $numRepeats
	 * @return array<string,string|int>
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	private function generateRepeats(int $numRepeats = -1): array
	{
		$repeats['LANG_REPEATS'] = $this->translator->translate('repeats', $this->moduleName);
		$repeats['LANG_NO_REPEATS'] = $this->translator->translate('no_repeats', $this->moduleName);
		$repeats['LANG_INFINITE_REPEATS'] = $this->translator->translate('infinite', $this->moduleName);
		$repeats['LANG_NUMBER_REPEATS'] =$this->translator->translate('number_repeats', $this->moduleName);
		switch ($numRepeats)
		{
			case -1:
				$repeats['NO_REPEATS_SELECT']      = 'checked';
				$repeats['REPEATS_VISIBILITY']     = 'visibility:hidden;';
				$repeats['NUMBER_REPEATS_DISPLAY'] = 'visibility:hidden;';
				$repeats['NUMBER_REPEATS_VALUE']   = 1;
				break;
			case 0:
				$repeats['INFINITE_REPEATS_SELECT'] = 'checked';
				$repeats['REPEATS_VISIBILITY']      = 'visibility:visible;';
				$repeats['NUMBER_REPEATS_DISPLAY']  = 'visibility:hidden;';
				$repeats['NUMBER_REPEATS_VALUE']    = 1;
				break;
			default:
				$repeats['NUMBER_REPEATS_SELECT']  = 'checked="checked"';
				$repeats['NUMBER_REPEATS_DISPLAY'] = 'visibility:visible;';
				$repeats['NUMBER_REPEATS_VALUE']   = $numRepeats;
				$repeats['REPEATS_VISIBILITY']     = 'visibility:visible;';
				break;
		}

		return $repeats;
	}

	/**
	 * @param int $minutes
	 * @return array<string,string|int>
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	private function generateMinutes(int $minutes = 0): array
	{
		return [
			'LANG_EVERY' => $this->translator->translate('every', $this->moduleName),
			'LANG_REPEAT_MINUTES' => $this->translator->translate('minutes', 'main'),
			'REPEAT_MINUTES' => $minutes
		];

	}

	/**
	 * @param int $hours
	 * @return array<string,string|int>
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	private function generateHours(int $hours = 0): array
	{
		return [
			'LANG_REPEAT_HOURS' => $this->translator->translate('hours', 'main'),
			'REPEAT_HOURS' => $hours
		];
	}

	/**
	 * @param int $days
	 * @return array<string,string|int>
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	private function generateDays(int $days = 0): array
	{
		return [
			'LANG_REPEAT_DAYS' => $this->translator->translate('days', 'main'),
			'REPEAT_DAYS' => $days
		];
	}

	/**
	 * @param int $weeks
	 * @return array<string,string|int>
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	private function generateWeeks(int $weeks = 0): array
	{
		return [
			'LANG_REPEAT_WEEKS' => $this->translator->translate('weeks', 'main'),
			'REPEAT_WEEKS' => $weeks
		];
	}

	/**
	 * @param int $months
	 * @return array<string,string|int>
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	private function generateMonths(int $months = 0): array
	{
		return [
			'LANG_REPEAT_MONTHS' => $this->translator->translate('months', 'main'),
			'REPEAT_MONTHS' => $months
		];

	}

	/**
	 * @param int $years
	 * @return array<string,string|int>
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	private function generateYears(int $years = 0): array
	{

		return [
			'LANG_REPEAT_YEARS' => $this->translator->translate('years', 'main'),
			'REPEAT_YEARS' => $years
		];

	}

	/**
	 * @param string $isoDate
	 * @return array<string,string>
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	private function generateDateTime(string $isoDate = ''): array
	{
		return [
			'LANG_DATETIME' => $this->translator->translate('begin_datetime', $this->moduleName),
			'DATETIME' => $isoDate
		];
	}

	/**
	 * @return array<string,mixed>|array<empty,empty>
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	private function generatePrefixDateTime(int $weekday = 0): array
	{
		$prefixDateTime = [];
		$langWeekdays = $this->translator->translate('weekday_selects', 'main');

		if ($weekday < 0)
			$positive_weekday = $weekday * -1;
		else
			$positive_weekday = $weekday;

		$wallclockWeekday = [];
		for ($i = 0; $i < 8; $i++)
		{
			$wallclockWeekday['WEEKDAY_NUMBER'] = $i;

			if ($i == 0)
				$iadea_weekday = 0;
			else if ($i == 7)
				$iadea_weekday = 1;
			else
				$iadea_weekday = $i + 1;

			if ($iadea_weekday == 0)
				$wallclockWeekday['WEEKDAY_NAME'] = '-';
			else
				$wallclockWeekday['WEEKDAY_NAME'] = $langWeekdays[$iadea_weekday];

			if ($i == $positive_weekday)
				$wallclockWeekday['WEEKDAY_NUMBER_SELECTED'] = 'selected';

			$wallclockWeekday[] = $wallclockWeekday;
		}
		$prefixDateTime['wallclock_weekday'] = $wallclockWeekday;


		$prefixDateTime['WEEKDAY_AFTER']  = $this->translator->translate('after', $this->moduleName);
		$prefixDateTime['WEEKDAY_BEFORE'] = $this->translator->translate('before', $this->moduleName);
		if ($weekday < 0)
			$prefixDateTime['SELECT_WEEKDAY_BEFORE'] =  'selected';
		else
			$prefixDateTime['SELECT_WEEKDAY_AFTER'] =  'selected';

		if ($weekday != 0)
			$prefixDateTime['WEEKDAY_PREFIX_DISPLAY'] =  'visibility:visible;';
		else
			$prefixDateTime['WEEKDAY_PREFIX_DISPLAY'] = 'visibility:hidden;';

		return $prefixDateTime;
	}

	/**
	 * @return array<string,string>
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	private function generateRemoveWallclock(): array
	{
		return [
			'LANG_REMOVE_WALLCLOCK' => $this->translator->translate('remove_wallclock', $this->moduleName),
			'LANG_REMOVE_1' => $this->translator->translate('delete', 'main')
		];
	}

}