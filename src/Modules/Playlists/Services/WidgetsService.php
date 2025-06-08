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

use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Services\AbstractBaseService;
use App\Framework\Utils\Widget\ConfigXML;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Log\LoggerInterface;
use Throwable;

class WidgetsService extends AbstractBaseService
{
	private readonly ItemsService $itemService;
	private readonly ConfigXml $configXml;
	private string $errorText = '';

	public function __construct(ItemsService $itemService, ConfigXml $configXml, LoggerInterface $logger)
	{
		$this->itemService = $itemService;
		$this->configXml = $configXml;
		parent::__construct($logger);
	}

	public function getErrorText(): string
	{
		return $this->errorText;
	}

	public function fetchWidgetByItemId(int $itemId): array
	{
		try
		{
			$item            = $this->fetchItem($itemId);
			$preferencesData = $this->determinePreferences($item['config_data']);

			$values = array();
			if (!is_null($item['content_data']))
			{
				$ar = unserialize($item['content_data']);
				if (is_array($ar))
				{
					$values = $ar;
				}
			}

			return [
				'item_id' => $itemId,
				'values' => $values,
				'preferences' => $preferencesData,
				'item_name' => $item['item_name']
			];
		}
		catch (Throwable $e)
		{
			$this->logger->error('Error widget fetch: ' . $e->getMessage());
			return [];
		}
	}

	public function saveWidget(int $itemId, array $requestData): bool
	{
		try
		{
			$item            = $this->fetchItem($itemId);
			$requestData     = $this->prepareContentData($item['config_data'], $requestData);
			$this->itemService->updateField($itemId, 'content_data', serialize($requestData));
			return true;
		}
		catch (Throwable $e)
		{
			$this->logger->error('Error save widget: ' . $e->getMessage());
			$this->errorText = $e->getMessage();
			return false;
		}
	}

	/**
	 * @throws ModuleException
	 * @throws FrameworkException
	 */
	public function prepareContentData($configData, $requestData, $init = false): string
	{
		$preferencesData = $this->determinePreferences($configData);

		foreach ($preferencesData as $key => $value)
		{
			$mandatory = (array_key_exists('mandatory', $value) && $value['mandatory'] === 'true');
			$has_key   = (array_key_exists($key, $requestData) && !empty($requestData[$key]));
			if (!$init && !$has_key && $mandatory)
				throw new ModuleException('items', $key. ' is mandatory field.');

			if (!$has_key)
				continue;

			switch($value['types'])
			{
				case 'colorOpacity':
				case 'integer':
					$requestData[$key]  = (int) $requestData[$key];
					break;
				default:
				case 'text':
				case 'radio':
				case 'color':
				case 'list':
				case 'combo':
					$requestData[$key]  = htmlspecialchars($requestData[$key], ENT_QUOTES);
					break;
			}
		}

		return serialize($requestData);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	private function fetchItem(int $itemId): array
	{
		$this->itemService->setUID($this->UID);
		$item = $this->itemService->fetchItemById($itemId);
		if ($item['mimetype'] !== 'application/widget')
			throw new ModuleException('items', 'Not a widget item.');

		return $item;
	}

	/**
	 * @throws ModuleException
	 * @throws FrameworkException
	 */
	private function determinePreferences(string $configData): array
	{
		if (!$this->configXml->load($configData)->hasEditablePreferences())
			throw new ModuleException('items', 'Widget has no editable preferences.');

		$this->configXml->parseBasic()->parsePreferences();

		return $this->configXml->getPreferences();
	}

}