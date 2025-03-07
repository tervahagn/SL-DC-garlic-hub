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
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Utils\FilteredList\BaseResults;
use App\Modules\Playlists\PlaylistMode;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use DateTime;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class ResultList extends BaseResults
{
		private readonly AclValidator $aclValidator;
		private readonly Config $config;
		private readonly int $UID;

	/**
	 * @param AclValidator $acl_validator
	 * @param Config $config
	 */
	public function __construct(AclValidator $aclValidator, Config $config)
	{
		$this->aclValidator = $aclValidator;
		$this->config = $config;
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function createFields($UID): static
	{
		$this->UID = $UID;
		$this->createField()->setName('playlist_name')->sortable(true);
		$this->addLanguageModule('playlists')->addLanguageModule('main');

		if ($this->aclValidator->isModuleAdmin($UID) || $this->aclValidator->isSubAdmin($UID))
			$this->createField()->setName('UID')->sortable(true);

		$this->createField()->setName('playlist_mode')->sortable(true);
		$this->createField()->setName('duration')->sortable(false);

		return $this;
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function renderTableBody($currentFilterResults, $showedIds, $usedPlaylists): array
	{
		$body = [];
		$selectableModes = $this->translator->translateArrayForOptions('playlist_mode_selects', 'playlists');
		foreach($currentFilterResults as $value)
		{
			$data            = [];
			$data['UNIT_ID'] = $value['playlist_id'];
			foreach($this->getTableHeaderFields() as $HeaderField)
			{
				$innerKey = $HeaderField->getName();
				$sort = $HeaderField->isSortable();

				$resultElement = [];
				$resultElement['CONTROL_NAME_BODY'] = $innerKey;
				switch ($innerKey)
				{
					case 'playlist_name':
						$data['if_editable'] = [
							'CONTROL_ELEMENT_VALUE_NAME'  => $value['playlist_name'],
							'CONTROL_ELEMENT_VALUE_TITLE' => $this->translator->translate('edit', 'main'),
							'CONTROL_ELEMENT_VALUE_LINK' => 'playlists/compose/'.$value['playlist_id'],
							'CONTROL_ELEMENT_VALUE_ID' => 'playlist_name_'.$value['playlist_id'],
							'CONTROL_ELEMENT_VALUE_CLASS' => ''
						];
						break;
					case 'UID':
						$data['if_UID'] = [
							'OWNER_UID'  => $value['UID'],
							'OWNER_NAME' => $value['username'],
						];
						break;
					case 'duration':
						$data['if_not_editable'] = [
							'CONTROL_ELEMENT_VALUE'  => $this->convertSeconds($value['duration'])->format('%H:%I:%S'),
						];
						break;
					case 'playlist_mode':
						$data['if_not_editable'] = [
							'CONTROL_ELEMENT_VALUE'  => $selectableModes[$value['playlist_mode']],
						];
						break;
					default:
						$data['if_not_editable'] = [
							'CONTROL_ELEMENT_VALUE'  => $value[$innerKey],
						];
						break;
				}
			}

/*
			$data['SELECT_DISABLED']     =  ($value['playlist_mode'] == PlaylistMode::MULTIZONE || $value['playlist_mode'] == PlaylistMode::EXTERNAL) ? 'disabled' : '';


			if ($value['UID'] == $this->UID ||
				$this->aclValidator->isModuleAdmin($this->UID) ||
				$this->aclValidator->isSubAdmin($this->UID))
			{

				$data['if_copy_option'] = [
					'LANG_ELEMENTS_COPY_LINK' => $this->translator->translate('copy_playlist', 'playlists'),
					'ELEMENTS_COPY_LINK' => 'playlists/?playlist_copy_id='.$value['playlist_id']
				];

				$data['if_edit_options'] = [
					'LANG_ELEMENTS_OPTION_LINK' => $this->translator->translate('edit_properties', 'main'),
					'ELEMENTS_OPTION_LINK' => 'playlists/settings/'.$value['playlist_id']
				];
			}

			if (!array_key_exists($value['playlist_id'], $usedPlaylists) &&
				$this->aclValidator->isAllowedToDeletePlaylist($this->UID, $value))
			{
				$data['if_deleteable'] = [
					'LANG_ELEMENTS_DELETE_LINK' => $this->translator->translate('delete', 'main'),
					'LANG_CONFIRM_DELETE'       => $this->translator->translate('confirm_delete', 'playlists'),
					'ELEMENTS_DELETE_LINK'     => 'playlists/?delete_id='.$value['playlist_id']
				];
			}
			*/
			$body[] = $data;
		}

		return ['elements_result_element' => $data];

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