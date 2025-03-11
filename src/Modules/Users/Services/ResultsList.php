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
use App\Framework\Utils\FilteredList\BaseResults;

class ResultsList extends BaseResults
{
	private readonly AclValidator $aclValidator;
	private readonly Config $config;
	private readonly int $UID;

	public function __construct(AclValidator $aclValidator, Config $config)
	{
		$this->aclValidator = $aclValidator;
		$this->config = $config;
	}

	public function createFields($UID): static
	{
		$this->UID = $UID;
		$this->addLanguageModule('users')->addLanguageModule('main');
		$this->createField()->setName('username')->sortable(true);
		$this->createField()->setName('created_at')->sortable(true);
		$this->createField()->setName('status')->sortable(true);
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
		return $body;
	}

}