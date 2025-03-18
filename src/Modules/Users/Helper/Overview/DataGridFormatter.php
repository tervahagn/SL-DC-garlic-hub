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

namespace App\Modules\Users\Helper\Overview;

use App\Framework\Core\Config\Config;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\DataGrid\FormatterServiceLocator;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Modules\Users\Services\AclValidator;
use DateTime;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class DataGridFormatter
{

	private FormatterServiceLocator $formatterServiceLocator;
	private Translator $translator;
	private AclValidator $aclValidator;
	private Config $config;

	public function __construct(FormatterServiceLocator $formatterServiceLocator, Translator $translator, AclValidator $aclValidator, Config $config)
	{
		$this->formatterServiceLocator = $formatterServiceLocator;
		$this->translator = $translator;
		$this->aclValidator = $aclValidator;
		$this->config = $config;
	}

	public function formatFilterForm(array $dataGridBuild): array
	{
		return $this->formatterServiceLocator->getFormBuilder()->formatForm($dataGridBuild);
	}

	public function configurePagination(BaseFilterParameters $parameters): void
	{
		$this->formatterServiceLocator->getPaginationFormatter()
			->setSite('users')
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
		$this->formatterServiceLocator->getHeaderFormatter()->configure($parameters, 'users', ['users', 'main']);
		return $this->formatterServiceLocator->getHeaderFormatter()->renderTableHeader($fields);
	}

	public function formatAdd(): array
	{
		return [
			'ADD_BI_ICON' => 'person-add',
			'LANG_ELEMENTS_ADD_LINK' =>	$this->translator->translate('add', 'users'),
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
	public function formatTableBody(array $currentFilterResults, array $fields, $currentUID): array
	{
		$body = [];
		foreach($currentFilterResults as $user)
		{
			$list = [];
			$list['UNIT_ID'] = $user['UID'];
			foreach ($fields as $HeaderField)
			{
				$innerKey = $HeaderField->getName();
				$sort = $HeaderField->isSortable();

				$resultElements = [];
				$resultElements['CONTROL_NAME_BODY'] = $innerKey;
				switch ($innerKey)
				{
					case 'username':
						if ($this->config->getEdition() == Config::PLATFORM_EDITION_EDGE)
							$resultElements['is_text'] = $this->formatterServiceLocator->getBodyFormatter()->renderText($user['username']);
						else
						{
							$resultElements['is_link'] = $this->formatterServiceLocator->getBodyFormatter()->renderLink(
								$user['username'],
								$this->translator->translate('edit', 'main'),
								'users/profile' . $user['UID'],
								'username_' . $user['UID']
							);

						}
						break;
					case 'status':
						$resultElements['is_text'] = $this->formatterServiceLocator->getBodyFormatter()->renderText(
							$this->translator->translateArrayForOptions('status_selects', 'users')[$user['status']]
						);
						break;
					default:
						$resultElements['is_text'] = $this->formatterServiceLocator->getBodyFormatter()->renderText($user[$innerKey]);
						break;
				}
				$list['elements_result_element'][] = $resultElements;

				if (
					$user['UID'] == $currentUID ||
					$this->aclValidator->isModuleAdmin($currentUID) ||
					$this->aclValidator->isSubAdmin($currentUID))
				{
					$list['has_action'] = [

						[
							'LANG_ACTION' => $this->translator->translate('edit_settings', 'users'),
							'LINK_ACTION' => 'users/settings/' . $currentUID,
							'ACTION_NAME' => 'edit',
							'ACTION_ICON_CLASS' => 'pencil'
						]
					];
					if ($this->aclValidator->isModuleAdmin($currentUID) && $user['status'] == 0)
					{
						$list['has_delete'] = [
							'LINK_DELETE_ACTION' => 'users/?delete_id=' . $user['UID'],
							'LANG_CONFIRM_DELETE' => $this->translator->translate('confirm_delete', 'users'),
							'DELETE_ID' => $user['UID']
						];
					}

				}
			}
			$body[] = $list;
		}
		return $body;
	}
}