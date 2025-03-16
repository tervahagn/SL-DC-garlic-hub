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

namespace App\Modules\Users\Services;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Utils\FilteredList\BaseResults;
use App\Framework\Utils\FilteredList\Results\ResultsManager;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class ResultsList extends BaseResults
{
	private readonly AclValidator $aclValidator;
	private readonly Config $config;
	private readonly int $UID;

	public function __construct(AclValidator $aclValidator, Config $config, ResultsManager $headerFieldFactory)
	{
		$this->aclValidator = $aclValidator;
		$this->config = $config;
		parent::__construct($headerFieldFactory);
	}

	public function createFields($UID): static
	{
		$this->UID = $UID;
		$this->addLanguageModule('users')->addLanguageModule('main');
		$this->createField()->setName('username')->sortable(true);
		$this->createField()->setName('created_at')->sortable(true);
		$this->createField()->setName('status')->sortable(false);
		if ($this->config->getEdition() === Config::PLATFORM_EDITION_CORE || $this->config->getEdition() === Config::PLATFORM_EDITION_ENTERPRISE)
		{
			$this->createField()->setName('firstname')->sortable(false);
			$this->createField()->setName('surname')->sortable(false);
			$this->createField()->setName('company_name')->sortable(false);
		}

		return $this;
	}


	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function renderTableBody($currentFilterResults): array
	{
		$body = [];
		foreach($currentFilterResults as $value)
		{
			$data = [];
			$data['UNIT_ID'] = $value['UID'];
			foreach ($this->getTableHeaderFields() as $HeaderField)
			{
				$innerKey = $HeaderField->getName();
				$sort = $HeaderField->isSortable();

				$resultElements = [];
				$resultElements['CONTROL_NAME_BODY'] = $innerKey;
				switch ($innerKey)
				{
					case 'username':
						if ($this->config->getEdition() == Config::PLATFORM_EDITION_EDGE)
						{
							$resultElements['if_not_editable'] = ['CONTROL_ELEMENT_VALUE' => $value['username']];

						}
						else
						{
							$resultElements['if_editable'] = [
								'CONTROL_ELEMENT_VALUE_NAME' => $value['username'],
								'CONTROL_ELEMENT_VALUE_TITLE' => $this->translator->translate('edit', 'main'),
								'CONTROL_ELEMENT_VALUE_LINK' => 'users/profile' . $value['UID'],
								'CONTROL_ELEMENT_VALUE_ID' => 'username_' . $value['UID'],
								'CONTROL_ELEMENT_VALUE_CLASS' => ''
							];
						}
						break;
					case 'status':
						$resultElements['if_not_editable'] = [
							'CONTROL_ELEMENT_VALUE' => $this->translator->translateArrayForOptions('status_selects', 'users')[$value['status']],
						];
						break;
					default:
						$resultElements['if_not_editable'] = ['CONTROL_ELEMENT_VALUE' => $value[$innerKey],];
						break;
				}
				$data['elements_result_element'][] = $resultElements;

				if (
					$value['UID'] == $this->UID ||
					$this->aclValidator->isModuleAdmin($this->UID) ||
					$this->aclValidator->isSubAdmin($this->UID))
				{
					$data['has_action'] = [

						[
							'LANG_ACTION' => $this->translator->translate('edit_settings', 'users'),
							'LINK_ACTION' => 'users/settings/' . $value['UID'],
							'ACTION_NAME' => 'edit',
							'ACTION_ICON_CLASS' => 'pencil'
						]
					];
					if ($this->aclValidator->isModuleAdmin($this->UID) && $value['status'] == 0)
					{
						$data['has_delete'] = [
							'LINK_DELETE_ACTION' => 'users/?delete_id=' . $value['UID'],
							'LANG_CONFIRM_DELETE' => $this->translator->translate('confirm_delete', 'users'),
							'DELETE_ID' => $value['UID']
						];
					}

				}
			}
			$body[] = $data;
		}
		return $body;
	}

}