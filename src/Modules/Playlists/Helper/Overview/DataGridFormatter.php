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

namespace App\Modules\Playlists\Helper\Overview;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\DataGrid\FormatterServiceLocator;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Modules\Playlists\Helper\PlaylistMode;
use App\Modules\Playlists\Services\AclValidator;
use DateTime;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class DataGridFormatter
{

	private FormatterServiceLocator $formatterServiceLocator;
	private Translator $translator;
	private AclValidator $aclValidator;

	public function __construct(FormatterServiceLocator $formatterServiceLocator, Translator $translator, AclValidator $aclValidator)
	{
		$this->formatterServiceLocator = $formatterServiceLocator;
		$this->translator = $translator;
		$this->aclValidator = $aclValidator;
	}


	public function formatFilterForm(array $dataGridBuild): array
	{
		return $this->formatterServiceLocator->getFormBuilder()->formatForm($dataGridBuild);
	}

	public function configurePagination(BaseFilterParameters $parameters): void
	{
		$this->formatterServiceLocator->getPaginationFormatter()
			->setSite('playlists')
			->setBaseFilter($parameters);
	}
	/**
	 * @throws ModuleException
	 */
	public function formatPaginationDropDown(array $dropDownSettings): array
	{
		return $this->formatterServiceLocator->getPaginationFormatter()->formatDropdown($dropDownSettings);
	}

	/**
	 * @throws ModuleException
	 */
	public function formatPaginationLinks(array $paginationLinks): array
	{
		return $this->formatterServiceLocator->getPaginationFormatter()->formatLinks($paginationLinks);
	}

	public function formatTableHeader(BaseFilterParameters $parameters, array $fields): array
	{
		$this->formatterServiceLocator->getHeaderFormatter()->configure($parameters, 'playlists', ['playlists', 'main']);
		return $this->formatterServiceLocator->getHeaderFormatter()->renderTableHeader($fields);
	}

	public function formatAdd(): array
	{
		return [
			'ADD_BI_ICON' => 'folder-plus',
			'LANG_ELEMENTS_ADD_LINK' =>	$this->translator->translate('add', 'playlists'),
			'ELEMENTS_ADD_LINK' => '#'

		];
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
	public function formatTableBody(array $currentFilterResults, array $fields, $usedPlaylists, $currentUID): array
	{
		$body = [];
		$selectableModes = $this->translator->translateArrayForOptions('playlist_mode_selects', 'playlists');
		foreach($currentFilterResults as $playlist)
		{
			$list            = [];
			$list['UNIT_ID'] = $playlist['playlist_id'];
			foreach($fields as $HeaderField)
			{
				$innerKey = $HeaderField->getName();

				$resultElements = [];
				$resultElements['CONTROL_NAME_BODY'] = $innerKey;
				switch ($innerKey)
				{
					case 'playlist_name':
						$resultElements['is_link'] = $this->formatterServiceLocator->getBodyFormatter()->renderLink(
							$playlist['playlist_name'],
							$this->translator->translate('edit', 'main'),
							'playlists/compose/'.$playlist['playlist_id'],
							'playlist_name_'.$playlist['playlist_id']
						);

						break;
					case 'UID':
						$resultElements['is_UID'] = $this->formatterServiceLocator->getBodyFormatter()->renderUID($playlist['UID'], $playlist['username']);

						break;
					case 'duration':
						$resultElements['is_text'] = $this->formatterServiceLocator->getBodyFormatter()->renderText($this->convertSeconds($playlist['duration']));
						break;
					case 'playlist_mode':
						$resultElements['is_text'] = $this->formatterServiceLocator->getBodyFormatter()->renderText($selectableModes[$playlist['playlist_mode']]);
						break;
					case 'selector':
						$resultElements['SELECT_DISABLED'] = ($playlist['playlist_mode'] == PlaylistMode::MULTIZONE || $playlist['playlist_mode'] == PlaylistMode::EXTERNAL) ? 'disabled' : '';
						break;
					default:
						$resultElements['is_text'] = $this->formatterServiceLocator->getBodyFormatter()->renderText($playlist[$innerKey]);
						break;
				}
				$list['elements_result_element'][] = $resultElements;

				if ($playlist['UID'] == $currentUID ||
					$this->aclValidator->isModuleAdmin($currentUID) ||
					$this->aclValidator->isSubAdmin($currentUID))
				{
					$list['has_action'] = [
						$this->formatterServiceLocator->getBodyFormatter()->renderAction(
							$this->translator->translate('copy_playlist', 'playlists'),
							'playlists/?playlist_copy_id='.$playlist['playlist_id'],
							'copy', 'copy'),
						$this->formatterServiceLocator->getBodyFormatter()->renderAction(
							$this->translator->translate('edit_settings', 'playlists'),
							'playlists/settings/'.$playlist['playlist_id'],
							'edit', 'pencil')
					];
					if (!array_key_exists($playlist['playlist_id'], $usedPlaylists) &&
						$this->aclValidator->isAllowedToDeletePlaylist($currentUID, $playlist))
					{
						$list['has_delete'] = $this->formatterServiceLocator->getBodyFormatter()->renderActionDelete(
							$this->translator->translate('delete', 'main'),
							$this->translator->translate('confirm_delete', 'playlists'),
							'playlists/?delete_id='.$playlist['playlist_id'],
							$playlist['playlist_id'],
							''
						);
					}

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
	public function formatPlaylistContextMenu(): array
	{
		$list = $this->translator->translateArrayForOptions('playlist_mode_selects', 'playlists');
		$data = [];
		foreach ($list as $key => $value)
		{
			$data[] = [
				'CREATE_PLAYLIST_MODE' => $key,
				'LANG_CREATE_PLAYLIST_MODE' => $value
			];
		}
		return $data;
	}

	function convertSeconds(string $seconds): string
	{
		$dtT = new DateTime("@$seconds");
		return (new DateTime("@0"))->diff($dtT)->format('%H:%I:%S');
	}
}