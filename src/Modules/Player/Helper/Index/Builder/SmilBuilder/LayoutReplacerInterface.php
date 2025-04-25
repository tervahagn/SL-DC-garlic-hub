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


namespace App\Modules\Player\Helper\Index\Builder\SmilBuilder;

use App\Modules\Playlists\Helper\PlaylistMode;

class LayoutReplacerInterface extends AbstractReplacer implements ReplacerInterface
{
	public function replace(): array
	{
		$properties = $this->playerEntity->getProperties();
		$layout = $this->replaceRootLayout($properties['width'], $properties['height']);
		if ($this->playerEntity->getPlaylistMode() == PlaylistMode::MULTIZONE->value)
		{
			$layout['regions'] = $this->replaceMultizoneRegions();
		}
		else
		{
			$layout['regions'][] = $this->replaceRegion('', 0, 0, $properties['width'], $properties['height'], 0);
		}
		return $layout;
	}

	private function replaceRootLayout(string $width, string $height): array
	{
		return [
			'ROOT_LAYOUT_WIDTH' => $width,
			'ROOT_LAYOUT_HEIGHT' => $height
		];
	}

	private function replaceMultizoneRegions(): array
	{
		$zones = $this->playerEntity->getMultizone();

		$regions = [];

		foreach ($zones['zones'] as $key => $value)
		{
			if ($zones['export_unit'] == 'percent')
			{
				$regions[] = $this->replaceRegion($key
					, $value['zone_top'] . '%', $value['zone_left'] . '%', $value['zone_width'] . '%', $value['zone_height'] . '%', $value['zone_z-index'], $value['zone_bgcolor']);
			}
			else
			{
				$regions[] = $this->replaceRegion(
					$key,
					$value['zone_top'], $value['zone_left'], $value['zone_width'], $value['zone_height'],
					$value['zone_z-index'], $value['zone_bgcolor']);
			}
		}
		return $regions;
	}

	private function replaceRegion($screen_id, $top, $left, $width, $height, $zIndex, $bgColor = 'transparent'): array
	{
		return [
			'SCREEN_ID' => $screen_id,
			'REGION_TOP' => $top,
			'REGION_LEFT' => $left,
			'REGION_WIDTH' => $width,
			'REGION_HEIGHT' => $height,
			'REGION_Z_INDEX' => $zIndex,
			'REGION_BGCOLOR' => $bgColor
		];
	}

}

