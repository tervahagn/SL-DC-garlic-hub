<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


namespace App\Modules\Player\Helper\Index\Builder\Sections;

use App\Modules\Player\Helper\PlayerModel;
use DateMalformedStringException;
use DateTime;
use Exception;

class ScreenTimesReplacer extends AbstractReplacer implements ReplacerInterface
{

	private string $current_date = 'now';
	private array $begin         = [];
	private array $end           = [];

	private array $screenTimes   = [];

	/**
	 * @throws Exception
	 */
	public function replace(): array
	{
		if ($this->playerEntity->getModel() != PlayerModel::GARLIC->value)
			return [];

		$this->determineStandByTimes($this->playerEntity->getScreenTimes());
		if (!$this->hasValues())
			return [];

		return [
				'BEGIN_WALLCLOCKS' => $this->getBeginWallClockString(),
				'END_WALLCLOCKS' => $this->getEndWallClockString()
			];
	}

	public function hasValues(): bool
	{
		if (empty($this->begin) || empty($this->end))
			return false;

		return true;
	}

	public function getBeginWallClockString(): string
	{
		return implode(';', $this->begin);
	}

	public function getEndWallClockString(): string
	{
		return implode(';', $this->end);
	}

	/**
	 * @throws DateMalformedStringException
	 */
	public function determineStandByTimes(array $screenTimes): static
	{
		if (!$this->isScreenTimeValid($screenTimes))
			return $this;

		$wallclock = $this->simplifyWallclockDatePart();

		foreach($this->screenTimes as $weekdays)
		{
			$w             = (((int) $weekdays['day'])+1);
			$day           = '+w'.$w.'T';
			foreach ($weekdays['periods'] as $value)
			{
				$this->end[] = $wallclock.$day.$value['start'].':00/P1W)';
				if ($value['end'] != '00:00')
					$this->begin[] = $wallclock.$day.$value['end'].':00/P1W)';
			}
		}

		if (empty($this->begin))
			$this->begin[] = $wallclock.'+w1T00:00:00/P1W)';
		if (empty($this->end))
			$this->end[] = $wallclock.'+w7T23:59:59/P1W)';

		return $this;
	}


	/**
	 * @throws DateMalformedStringException
	 */
	private function simplifyWallclockDatePart(): string
	{
		// to make calculation in player easier
		$date = new DateTime($this->current_date);
		$date->modify('-1 month');

		return 'wallclock(R/'.$date->format('Y-m-d');
	}

	private function isScreenTimeValid(array $screenTimes): bool
	{
		if (empty($screenTimes))
			return false;

		$this->screenTimes = $screenTimes;
		return true;
	}


}