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

/**
 * Class for exporting to SMIL container tags (seq, par, excl, priorityClass)
 */
class Container extends Base implements ItemInterface
{
	protected array $properties = [];

	public function getExclusive(): string
	{
		if (!$this->hasBeginTrigger())
			return '';

		$this->trigger = $this->determineBeginEndTrigger();
		$ret           = "\t\t\t".'<priorityClass>'."\n";
		$ret          .= $this->getSequentialTag();
		$ret          .= "\t\t\t".'</priorityClass>'."\n";
		$this->trigger = '';

		return $ret;
	}

	public function getElement(): string
	{
		if ($this->hasBeginTrigger())
			return '';

		$ret =	$this->getSequentialTag();
		return $ret;
	}

	public function getSequentialTag(): string
	{
		return 	"\t\t\t\t".'<seq '.$this->setExprAttribute().$this->trigger.'title="'.$this->encodeItemNameForTitleTag().'">'."\n".
		'{ITEMS_'.$this->item['file_resource'].'}'."\n".
		"\t\t\t\t".'</seq>'."\n";
	}

	public function getElementLink(): string
	{
		$ret = '';
		if ($this->properties['scheduled_start_date'] == '0000-00-00')
			$ret = '{ITEMS_0#'.$this->item['external_link'].'}'."\n";

		return $ret;
	}

	public function getPrefetch(): string
	{
		return '{PREFETCH_'.$this->item['file_resource'].'}'."\n";
	}

	public function getElementForPreview(): string
	{
		return '<seq id="playlist_'.$this->item['media_id'].'" title="'.$this->item['media_id'].'"/>'."\n";
	}

	protected function getProperties(): static
	{
		if (!is_array($this->item['properties']) ||
			    empty($this->item['properties']))
		{
			$this->item['properties'] = array(
				'scheduled_start_date'  => '0000-00-00',
				'scheduled_start_time'  => '00:00:00',
				'repeat_counts' => 0,
				'repeat_minutes' => 0,
				'repeat_hours' => 0,
				'repeat_days' => 0,
				'repeat_weeks' => 0,
				'repeat_months' => 0
			);
		}

		$this->properties = $this->item['properties'];
		return $this;
	}


}