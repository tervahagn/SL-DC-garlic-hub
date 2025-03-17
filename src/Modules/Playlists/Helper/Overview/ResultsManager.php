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

use App\Framework\Core\Config\Config;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FilteredList\Results\ResultsServiceLocator;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Modules\Playlists\Helper\PlaylistMode;
use App\Modules\Playlists\Services\AclValidator;
use DateTime;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class ResultsManager
{

	private ResultsServiceLocator $resultsServiceLocator;
	private Translator $translator;
	private AclValidator $aclValidator;
	private int $UID;
	private Config $config;

	public function __construct(ResultsServiceLocator $resultsServiceLocator, Translator $translator, AclValidator $aclValidator, Config $config)
	{
		$this->resultsServiceLocator = $resultsServiceLocator;
		$this->translator = $translator;
		$this->aclValidator = $aclValidator;
		$this->config = $config;
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function createTableFields(int $UID): static
	{
		$this->UID = $UID;

		$this->resultsServiceLocator->getCreator()->createField('playlist_name', true);

		if ($this->config->getEdition() !== Config::PLATFORM_EDITION_EDGE &&
			($this->aclValidator->isModuleAdmin($this->UID) || $this->aclValidator->isSubAdmin($this->UID)))
		{
			$this->resultsServiceLocator->getCreator()->createField('UID', true);
		}

		$this->resultsServiceLocator->getCreator()->createField('playlist_mode', true);
		$this->resultsServiceLocator->getCreator()->createField('duration', false);

		return $this;
	}

	public function createPagination(BaseFilterParameters $parameters, int $resultCount): void
	{
		$this->resultsServiceLocator->getPaginationManager()->init($parameters, 'playlists')
			->createPagination($resultCount)
			->createDropDown();
	}

	/**
	 * @throws ModuleException
	 */
	public function renderPaginationDropDown(): array
	{
		return $this->resultsServiceLocator->getPaginationManager()->renderPaginationDropDown();
	}

	/**
	 * @throws ModuleException
	 */
	public function renderPaginationLinks(): array
	{
		return $this->resultsServiceLocator->getPaginationManager()->renderPaginationLinks();
	}

	public function renderTableHeader($parameters): array
	{
		$fields = $this->resultsServiceLocator->getCreator()->getTableHeaderFields();
		$this->resultsServiceLocator->getHeaderRenderer()->configure($parameters, 'playlists', ['playlists', 'main']);
		return $this->resultsServiceLocator->getHeaderRenderer()->renderTableHeader($fields);
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
	public function renderTableBody(array $currentFilterResults, $usedPlaylists): array
	{
		$body = [];
		$selectableModes = $this->translator->translateArrayForOptions('playlist_mode_selects', 'playlists');
		foreach($currentFilterResults as $value)
		{
			$data            = [];
			$data['UNIT_ID'] = $value['playlist_id'];
			foreach($this->resultsServiceLocator->getCreator()->getTableHeaderFields() as $HeaderField)
			{
				$innerKey = $HeaderField->getName();

				$resultElements = [];
				$resultElements['CONTROL_NAME_BODY'] = $innerKey;
				switch ($innerKey)
				{
					case 'playlist_name':
						$resultElements['is_link'] = $this->resultsServiceLocator->getBodyRenderer()->renderLink(
							$value['playlist_name'],
							$this->translator->translate('edit', 'main'),
							'playlists/compose/'.$value['playlist_id'],
							'playlist_name_'.$value['playlist_id']
						);

						break;
					case 'UID':
						$resultElements['is_UID'] = $this->resultsServiceLocator->getBodyRenderer()->renderUID($value['UID'], $value['username']);

						break;
					case 'duration':
						$resultElements['is_text'] = $this->resultsServiceLocator->getBodyRenderer()->renderText($this->convertSeconds($value['duration']));
						break;
					case 'playlist_mode':
						$resultElements['is_text'] = $this->resultsServiceLocator->getBodyRenderer()->renderText($selectableModes[$value['playlist_mode']]);
						break;
					case 'selector':
						$resultElements['SELECT_DISABLED'] = ($value['playlist_mode'] == PlaylistMode::MULTIZONE || $value['playlist_mode'] == PlaylistMode::EXTERNAL) ? 'disabled' : '';
						break;
					default:
						$resultElements['is_text'] = $this->resultsServiceLocator->getBodyRenderer()->renderText($value[$innerKey]);
						break;
				}
				$data['elements_result_element'][] = $resultElements;

				if ($value['UID'] == $this->UID ||
					$this->aclValidator->isModuleAdmin($this->UID) ||
					$this->aclValidator->isSubAdmin($this->UID))
				{
					$data['has_action'] = [
						$this->resultsServiceLocator->getBodyRenderer()->renderAction(
							$this->translator->translate('copy_playlist', 'playlists'),
							'playlists/?playlist_copy_id='.$value['playlist_id'],
							'copy', 'copy'),
						$this->resultsServiceLocator->getBodyRenderer()->renderAction(
							$this->translator->translate('edit_settings', 'playlists'),
							'playlists/settings/'.$value['playlist_id'],
							'edit', 'pencil')
					];
					if (!array_key_exists($value['playlist_id'], $usedPlaylists) &&
						$this->aclValidator->isAllowedToDeletePlaylist($this->UID, $value))
					{
						$data['has_delete'] = $this->resultsServiceLocator->getBodyRenderer()->renderActionDelete(
							$this->translator->translate('delete', 'main'),
							$this->translator->translate('confirm_delete', 'playlists'),
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

	static function convertSeconds(string $seconds): string
	{
		$dtT = new DateTime("@$seconds");
		return (new DateTime("@0"))->diff($dtT)->format('%H:%I:%S');
	}


}