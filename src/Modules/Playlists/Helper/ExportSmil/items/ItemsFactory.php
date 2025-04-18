<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Helper\ItemType;

class ItemsFactory
{
	private Config $config;

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public function createItem($item)
	{
		switch ($item['item_type'])
		{
			case ItemType::MEDIAPOOL->value:
				return new Media($this->config, $item);
			case ItemType::PLAYLIST->value:
				return new Container($this->config, $item);
			case ItemType::TEMPLATE->value:
				return new Template($this->config, $item);
			case ItemType::CHANNEL->value:
				return new Channel($this->config, $item);
			default:
				new ModuleException('playlists', 'Unknown item type '. $item['item_type'].'.');

		}

	}
}