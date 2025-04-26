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

/**
 * The refresh of the content look at http://www.a-smil.org/index.php/Fixed_Playlist,_Dynamic_Content
 * needs to be greater than playlist or refresh cause otherwise the playlist restarts
 * from beginning in some IAdea-Players and never playing the list until end.
 * So it is min 900s or the double duration of playlist/Refresh
 */
class PrefetchRefreshReplacer extends AbstractReplacer implements ReplacerInterface
{
	public function replace(): string
	{
		return $this->calculateTime();
	}

	public function calculateTime(): string
	{
		if ($this->playerEntity->getDuration() < $this->playerEntity->getRefresh())
		{
			$duration = $this->playerEntity->getRefresh() + 1;
		}
		else
		{
			$duration = $this->playerEntity->getDuration()  + 1;
		}

		if ($duration < 900)
			$duration = 900;
		else
			$duration = $duration * 2;

		return $duration;
	}
}