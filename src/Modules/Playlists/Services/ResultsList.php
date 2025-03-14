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

use App\Framework\Core\Config\Config;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Utils\FilteredList\Results\BaseResults;
use App\Framework\Utils\FilteredList\Results\ResultsServiceLocator;
use App\Modules\Playlists\PlaylistMode;
use DateTime;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class ResultsList extends BaseResults
{
		private readonly AclValidator $aclValidator;
		private readonly Config $config;
		private readonly int $UID;

	/**
	 * @param AclValidator $acl_validator
	 * @param Config $config
	 */
	public function __construct(AclValidator $aclValidator, Config $config, ResultsServiceLocator $resultsServiceLocator)
	{
		$this->aclValidator = $aclValidator;
		$this->config       = $config;

		parent::__construct($resultsServiceLocator);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function createFields($UID): static
	{
		$this->UID = $UID;
		$this->createField('playlist_name', true);
		$this->addLanguageModule('playlists')->addLanguageModule('main');

		if ($this->aclValidator->isModuleAdmin($UID) || $this->aclValidator->isSubAdmin($UID))
			$this->createField('UID', true);

		$this->createField('playlist_mode', true);
		$this->createField('duration', false);

		return $this;
	}

	/**
	 * @throws CoreException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \App\Framework\Exceptions\ModuleException
	 */
	public function renderTableBody(Translator $translator, $showedIds, $usedPlaylists): array
	{
		$body = [];
		$selectableModes = $translator->translateArrayForOptions('playlist_mode_selects', 'playlists');
		foreach($this->getCurrentFilterResults() as $value)
		{
			$data            = [];
			$data['UNIT_ID'] = $value['playlist_id'];
			foreach($this->getTableHeaderFields() as $HeaderField)
			{
				$innerKey = $HeaderField->getName();
				$sort = $HeaderField->isSortable();

				$resultElements = [];
				$resultElements['CONTROL_NAME_BODY'] = $innerKey;
				switch ($innerKey)
				{
					case 'playlist_name':
						$resultElements['is_link'] = $this->resultsServiceLocator->getRenderer()->renderLink(
							$value['playlist_name'],
							$translator->translate('edit', 'main'),
							'playlists/compose/'.$value['playlist_id'],
							'playlist_name_'.$value['playlist_id']
						);

						break;
					case 'UID':
						$resultElements['is_UID'] = $this->resultsServiceLocator->getRenderer()->renderUID($value['UID'], $value['username']);

						break;
					case 'duration':
						$resultElements['is_text'] = $this->resultsServiceLocator->getRenderer()->renderText($this->convertSeconds($value['duration'])->format('%H:%I:%S'));
						break;
					case 'playlist_mode':
						$resultElements['is_text'] = $this->resultsServiceLocator->getRenderer()->renderText($selectableModes[$value['playlist_mode']]);
						break;
					case 'selector':
						$resultElements['SELECT_DISABLED'] = ($value['playlist_mode'] == PlaylistMode::MULTIZONE || $value['playlist_mode'] == PlaylistMode::EXTERNAL) ? 'disabled' : '';
						break;
					default:
						$resultElements['is_text'] = $this->resultsServiceLocator->getRenderer()->renderText($value[$innerKey]);
						break;
				}
				$data['elements_result_element'][] = $resultElements;

				if ($value['UID'] == $this->UID ||
					$this->aclValidator->isModuleAdmin($this->UID) ||
					$this->aclValidator->isSubAdmin($this->UID))
				{
					$data['has_action'] = [
						$this->resultsServiceLocator->getRenderer()->renderAction(
							$translator->translate('copy_playlist', 'playlists'),
							'playlists/?playlist_copy_id='.$value['playlist_id'],
							'copy', 'copy'),
						$this->resultsServiceLocator->getRenderer()->renderAction(
							$translator->translate('edit_settings', 'playlists'),
							'playlists/settings/'.$value['playlist_id'],
							'edit', 'pencil')
					];
					if (!array_key_exists($value['playlist_id'], $usedPlaylists) &&
						$this->aclValidator->isAllowedToDeletePlaylist($this->UID, $value))
					{
						$data['has_delete'] = $this->resultsServiceLocator->getRenderer()->renderActionDelete(
							$translator->translate('delete', 'main'),
							$translator->translate('confirm_delete', 'playlists'),
							'playlists/?delete_id='.$value['playlist_id'],
							$value['playlist_id'],
							''
						);
					}

				}
			}
			$body[] = $data;
		}

		return $body;

/*		$data['LANG_SELECT_ALL', 	$Translator->translate('select_all', 'main'));
		$data['LANG_DESELCT_ALL', 	$Translator->translate('deselect_all', 'main'));
		foreach ($this->translator->getTranslationsArrayForOptions('action_selects', 'playlists') as $key => $action)
		{
			$data['select_action' ] = [
				'OPTION_SELECTED_ACTION_ID' => $key,
				'OPTION_SELECTED_ACTION_NAME' => $action
			];
		}
*/
	}

	private function convertSeconds($seconds): \DateInterval|false
	{
		$dtT = new DateTime("@$seconds");
		return (new DateTime("@0"))->diff($dtT);
	}

}