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

namespace App\Modules\Player\Helper\Datatable;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Datatable\AbstractDatatablePreparer;
use App\Framework\Utils\Datatable\PrepareService;
use App\Modules\Player\Enums\PlayerStatus;
use App\Modules\Player\Services\AclValidator;
use DateTime;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class DatatablePreparer extends AbstractDatatablePreparer
{
	private AclValidator $aclValidator;

	public function __construct(PrepareService $prepareService, AclValidator $aclValidator, Parameters $parameters)
	{
		$this->aclValidator = $aclValidator;
		parent::__construct('player', $prepareService, $parameters);
	}

	/**
	 * This method is cringe, but I do not have a better idea without starting over engineering
	 *
	 * @throws CoreException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws InvalidArgumentException
	 */
	public function prepareTableBody(array $currentFilterResults, array $fields, $currentUID): array
	{
		$body = [];
		foreach($currentFilterResults as $player)
		{
			$list            = [];
			$list['UNIT_ID'] = $player['player_id'];
			foreach($fields as $HeaderField)
			{
				$innerKey = $HeaderField->getName();

				$resultElements = [];
				$resultElements['CONTROL_NAME_BODY'] = $innerKey;
				switch ($innerKey)
				{
					case 'UID':
						$resultElements['is_UID'] = $this->prepareService->getBodyPreparer()->formatUID($player['UID'], $player['username']);
						break;
					case 'status':
						$resultElements['is_text'] = $this->prepareService->getBodyPreparer()->formatText($this->translator->translateArrayForOptions('status_selects', 'player')[$player['status']]);
						break;
					case 'model':
						$resultElements['is_text'] = $this->prepareService->getBodyPreparer()->formatText($this->translator->translateArrayForOptions('model_selects', 'player')[$player['model']]);
						break;
					case 'playlist_id':
						if ($player['status'] == PlayerStatus::RELEASED->value)
						{
							$resultElements['is_text'] = $this->prepareService->getBodyPreparer()->formatText($player['playlist_name']);
						}
						else
						{
							$resultElements['is_text'] = '';
						}
						break;
					default:
						$resultElements['is_text'] = $this->prepareService->getBodyPreparer()->formatText($player[$innerKey]);
						break;
				}
				$list['elements_result_element'][] = $resultElements;
			}
			if ($player['status'] == PlayerStatus::RELEASED->value)
			{
				$translation = $this->translator->translate('select_playlist', 'player');
				$list['has_action'][] = $this->prepareService->getBodyPreparer()->formatAction(
					$translation,'#','edit', $player['playlist_id'], 'pencil select-playlist'
				);

				if ($player['playlist_id'] > 0)
				{
					$translation = $this->translator->translate('remove_playlist', 'player');

					$list['has_action'][] = $this->prepareService->getBodyPreparer()->formatAction(
						$translation, '#','playlist', $player['playlist_id'], 'x-circle remove-playlist'
					);

					$translation = $this->translator->translate('goto_playlist', 'player');
					$link = '/playlists/compose/' . $player['playlist_id'];

					$list['has_action'][] = $this->prepareService->getBodyPreparer()->formatAction(
						$translation, $link, 'playlist', $player['playlist_id'], 'music-note-list playlist-link'
					);
				}
			}
			$body[] = $list;
		}

		return $body;

/*		$data['LANG_SELECT_ALL', 	$this->translator->translate('select_all', 'main'));
		$data['LANG_DESELECT_ALL', 	$this->translator->translate('deselect_all', 'main'));
		foreach ($this->translator->getTranslationsArrayForOptions('action_selects', 'playlists') as $key => $action)
		{
			$data['select_action' ] = [
				'OPTION_SELECTED_ACTION_ID' => $key,
				'OPTION_SELECTED_ACTION_NAME' => $action
			];
		}
*/
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws InvalidArgumentException
	 */
	public function formatPlayerContextMenu(): array
	{
		$list = $this->translator->translateArrayForOptions('settings_selects', 'player');
		$data = [];
		//$edition = $this->aclValidator->getConfig()->getEdition();
		foreach ($list as $key => $value)
		{
			$data[] = [
				'PLAYER_SETTINGS' => $key,
				'LANG_PLAYER_SETTINGS' => $value
			];
		}
		return $data;
	}


/*
	private function convertSeconds(string $seconds): string
	{
		$dtT = new DateTime("@$seconds");
		return (new DateTime("@0"))->diff($dtT)->format('%H:%I:%S');
	}
*/
}