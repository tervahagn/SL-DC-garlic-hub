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

namespace App\Modules\Playlists\Helper\ExportSmil\items;

use App\Framework\Core\Config\Config;
use DateTime;

class Base
{
	protected array $item = [];
	protected readonly Config $config;

	protected bool $isMaster = false;
	protected string $trigger = '';
	protected array $properties = [];

	public function __construct(Config $config, array $item)
	{
		$this->config = $config;
		$this->item   = $item;
		$this->getProperties();
	}

	public function setIsMasterPlaylist(bool $is): static
	{
		$this->isMaster = $is;
		return $this;
	}

	protected function determineBeginEndTrigger(): string
	{
		$ret = 'begin="'.$this->determineTrigger($this->item['begin_trigger']).'" ';

		if ($this->hasEndTrigger())
			$ret .= 'end="'.$this->determineTrigger($this->item['end_trigger']).'" ';

		return $ret;
	}

	protected function determineTrigger(array $triggers): string
	{
		$ar = [];
		if (array_key_exists('wallclocks', $triggers))
			$ar = array_merge($ar, $this->parseWallClocks($triggers['wallclocks']));
		if (array_key_exists('accesskeys', $triggers))
			$ar = array_merge($ar, $this->parseAccesskeys($triggers['accesskeys']));
		if (array_key_exists('touches', $triggers))
			$ar = array_merge($ar, $this->parseTouches($triggers['touches']));
		if (array_key_exists('notifies', $triggers))
			$ar = array_merge($ar, $this->parseNotifies($triggers['notifies']));

		return implode(';', $ar);
	}

	protected function parseWallClocks(array $wallclocks): array
	{
		$determined = [];
		foreach ($wallclocks as $wallclock)
		{
			$determined[] = $this->determineOneWallClock($wallclock);
		}

		return $determined;
	}

	protected function parseAccesskeys(array $accessKeys): array
	{
		$determined = array();
		foreach ($accessKeys as $accessKey)
		{
			$determined[] = 'accesskey('.$accessKey['accesskey'].')';
		}

		return $determined;
	}

	protected function parseTouches(array $touches): array
	{
		$determined = array();
		foreach ($touches as $touch)
		{
			$determined[] = $touch['touch_item_id'].'.activateEvent';
		}

		return $determined;
	}

	protected function parseNotifies($notifies): array
	{
		$determined = array();
		foreach ($notifies as $notify)
		{
			$determined[] = 'notify('.$notify['notify'].')';
		}

		return $determined;
	}

	protected function determineOneWallClock(array $wallclock): string
	{
		$repeats = $intervals = $weekday ='';
		if ($wallclock['repeat_counts'] != -1)
		{
			if ($wallclock['repeat_counts'] == 0)
				$repeats = 'R/';
			else
				$repeats = 'R'.$wallclock['repeat_counts'].'/';

			$intervals = '/P';
			if ($wallclock['repeat_years'] > 0)
				$intervals .= $wallclock['repeat_years'].'Y';
			if ($wallclock['repeat_months'] > 0)
				$intervals .= $wallclock['repeat_months'].'M';
			if ($wallclock['repeat_weeks'] > 0)
				$intervals .= $wallclock['repeat_weeks'].'W';
			if ($wallclock['repeat_days'] > 0)
				$intervals .= $wallclock['repeat_days'].'D';
			if ($wallclock['repeat_hours'] > 0 OR $wallclock['repeat_minutes'] > 0)
			{
				$intervals .= 'T';
				if ($wallclock['repeat_hours'] > 0)
					$intervals .= $wallclock['repeat_hours'].'H';
				if ($wallclock['repeat_minutes'] > 0)
					$intervals .= $wallclock['repeat_minutes'].'M';
			}

			if (strlen($intervals) == 2)
			{
				$intervals = '';
				$repeats = '';
			}
		}
		if ($wallclock['weekday'] != 0 && $wallclock['weekday'] >= -7 && $wallclock['weekday'] <= 7)
			$weekday = $wallclock['weekday'][0].'w'.$wallclock['weekday'][1];

		return 'wallclock('.$repeats.$wallclock['iso_date'].$weekday.$intervals.')';
	}


	/**
	 * Ampersand (&) in title tag can cause an XML validation error
	 * Bug occured in garlic-player Android Rewe 2021-09-17
	 */
	protected function encodeItemNameForTitleTag(): string
	{
		return htmlspecialchars($this->item['item_name'], ENT_XML1);
	}

	protected function getProperties(): static
	{
		$this->properties = [];
		return $this;
	}

	protected function hasBeginTrigger(): bool
	{
		if (empty($this->item['begin_trigger']))
			return false;

		return (is_array($this->item['begin_trigger']) && count($this->item['begin_trigger']) > 0);
	}

	/**
	 * @return bool
	 */
	protected function hasEndTrigger(): bool
	{
		if (empty($this->item['end_trigger']))
			return false;

		return (is_array($this->item['end_trigger']) && count($this->item['end_trigger']) > 0);
	}

	/**
	 * @return string
	 */
	protected function setExprAttribute(): string
	{
		if (empty($this->item['conditional']))
			return '';

		$conditional = unserialize($this->item['conditional']);

		$expr = '';
		if ($conditional['date_from'] != '0000-00-00')
		{
			$expr .= "adapi-compare(substring-before(adapi-date(), 'T'), '".$this->item['date_from']."')&gt;=0";
		}
		if ($conditional['date_until'] != '0000-00-00')
		{
			if ($expr != '')
				$expr .= ' and ';
			$expr .= "adapi-compare(substring-before(adapi-date(), 'T'), '".$this->item['date_until']."')&lt;=0";
		}
		if ($conditional['time_from'] != '00:00:00')
		{
			if ($expr != '')
				$expr .= ' and ';
			$expr .= "adapi-compare(substring-after(adapi-date(), 'T'), '".$this->item['time_from']."')&gt;=0";
		}
		if ($conditional['time_until'] != '00:00:00')
		{
			if ($expr != '')
				$expr .= ' and ';
			if ($conditional['time_until'] == '00:00:00')
				$time_until = '23:59:59';
			else
				$time_until = $conditional['time_until'];
			$expr .= "adapi-compare(substring-after(adapi-date(), 'T'), '".$time_until."')&lt;=0";
		}
		$weektimes = '';

		$this->initialiseWeekTimes();

		if (count($conditional['weektimes']) > 0)
		{
			if ($expr != '')
			{
				$expr .= ' and ';
			}
			$j = 0;
			for ($i = 0; $j < 128; $j=pow(2, $i++))
			{
				if(isset($conditional['weektimes'][$j]))
				{
					if ($weektimes != '')
						$weektimes .= ' or ';

					$from = $this->convertSeconds($conditional['weektimes'][$j]['from']*15*60);
					$until = $this->convertSeconds($conditional['weektimes'][$j]['until']*15*60);
					if ($until == '00:00:00')
						$until = '23:59:59';
					$weektimes .= "(".($i-1)."=adapi-weekday() and adapi-compare(substring-after(adapi-date(), 'T'), '$from')&gt;=0 and adapi-compare(substring-after(adapi-date(), 'T'), '$until')&lt;=0)";
				}
			}
			if ($weektimes != '')
				$weektimes = '('.$weektimes.')';
		}
		$expr = $expr.$weektimes;

		if ($expr != '')
			$expr = 'expr="'.$expr.'" ';

		return $expr;
	}

	protected function convertSeconds($seconds): \DateInterval
	{
		$dtT = new DateTime("@$seconds");
		return (new DateTime("@0"))->diff($dtT);
	}

	private function initialiseWeekTimes(): void
	{
		if (!is_array($this->item['weektimes']) || empty($this->item['weektimes']))
			$this->item['weektimes'] = array();
	}

}