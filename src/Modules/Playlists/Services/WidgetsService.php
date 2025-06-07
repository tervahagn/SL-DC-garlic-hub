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


namespace App\Modules\Playlists\Services;

use App\Framework\Exceptions\ModuleException;
use App\Framework\Services\AbstractBaseService;
use App\Framework\Utils\Widget\ConfigXML;
use Psr\Log\LoggerInterface;
use Throwable;

class WidgetsService extends AbstractBaseService
{
	private readonly ItemsService $itemService;
	private readonly ConfigXml $configXml;

	public function __construct(ItemsService $itemService, ConfigXml $configXml, LoggerInterface $logger)
	{
		$this->itemService = $itemService;
		$this->configXml = $configXml;
		parent::__construct($logger);
	}

	public function fetchWidgetByItemId(int $itemId): array
	{
		try
		{
			$this->itemService->setUID($this->UID);
			$item = $this->itemService->fetchItemById($itemId);
			if ($item['mimetype'] !== 'application/widget')
				throw new ModuleException('items', 'Not a widget item.');

			if (!$this->configXml->load($item['config_data'])->hasEditablePreferences())
				throw new ModuleException( 'items', 'Widget has no editable preferences.');

			$this->configXml->parseBasic()->parsePreferences();
			$preferencesData = $this->configXml->getPreferences();

			$values = array();
			if (!is_null($item['content_data']))
			{
				$ar = unserialize($item['content_data']);
				if (is_array($ar))
					$values = $ar;
			}

			return [
				'item_id'      => $itemId,
				'values'       => $values,
				'preferences'  => $preferencesData,
				'item_name'    => $item['item_name']
			];
		}
		catch (Throwable $e)
		{
			$this->logger->error('Error item reorder: ' . $e->getMessage());
			return [];
		}




	}



}