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

namespace App\Modules\Playlists\Helper\ExportSmil\Utils;

class Conditional
{
	/** @var array<string,mixed>  */
	private array $conditional;

	/**
	 * @param array<string,mixed> $conditional
	 */
	public function __construct(array $conditional)
	{
		$this->conditional = $conditional;
	}

	public function hasConditional(): bool
	{
		return !empty($this->conditional);
	}

	public function determineExprAttribute(): string
	{
		if (!$this->hasConditional())
			return '';

		$expr = '';
		if ($this->conditional['date_from'] != '0000-00-00')
		{
			$expr .= "adapi-compare(substring-before(adapi-date(), 'T'), '".$this->conditional['date_from']."')&gt;=0";
		}
		if ($this->conditional['date_until'] != '0000-00-00')
		{
			if ($expr != '')
				$expr .= ' and ';

			$expr .= "adapi-compare(substring-before(adapi-date(), 'T'), '".$this->conditional['date_until']."')&lt;=0";
		}
		if ($this->conditional['time_from'] != '00:00:00')
		{
			if ($expr != '')
				$expr .= ' and ';

			$expr .= "adapi-compare(substring-after(adapi-date(), 'T'), '".$this->conditional['time_from']."')&gt;=0";
		}

		$time_until = $this->conditional['time_until'] == '00:00:00' ? '23:59:59' : $this->conditional['time_until'];
		if ($this->conditional['time_until'] != '00:00:00')
		{
			if ($expr != '')
				$expr .= ' and ';

			$time_until = $this->conditional['time_until'];
			$expr .= "adapi-compare(substring-after(adapi-date(), 'T'), '".$time_until."')&lt;=0";
		}
		$weektimes = '';

		$this->initialiseWeekTimes();

		if (count($this->conditional['weektimes']) > 0)
		{
			if ($expr != '')
				$expr .= ' and ';

			$j = 0;
			for ($i = 0; $j < 128; $j=pow(2, $i++))
			{
				if(isset($this->conditional['weektimes'][$j]))
				{
					if ($weektimes != '')
						$weektimes .= ' or ';

					$from =  gmdate("H:i:s",$this->conditional['weektimes'][$j]['from']*15*60);
					$until = gmdate("H:i:s",$this->conditional['weektimes'][$j]['until']*15*60);
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

	private function initialiseWeekTimes(): void
	{
		if (!is_array($this->conditional['weektimes']) || empty($this->conditional['weektimes']))
			$this->conditional['weektimes'] = [];
	}
}