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

use App\Framework\Services\AbstractBaseService;
use App\Framework\Utils\FormParameters\Traits\SearchFilterParams;
use App\Modules\Users\Repositories\Edge\UserMainRepository;

class UsersOverviewService extends AbstractBaseService
{
	use SearchFilterParams;
	private readonly UserMainRepository $userMainRepository;
	private readonly AclValidator $aclValidator;

	/**
	 * @param UserMainRepository $userMainRepository
	 * @param AclValidator $aclValidator
	 */
	public function __construct(UserMainRepository $userMainRepository, AclValidator $aclValidator)
	{
		$this->userMainRepository = $userMainRepository;
		$this->aclValidator = $aclValidator;
	}

	public function loadUserForOverview(FilterParameters $parameters): void
	{
		if ($this->aclValidator->isModuleAdmin($this->UID))
		{
			$this->handleRequestModuleAdmin($this->playlistsRepository, $parameters);
		}
		elseif ($this->aclValidator->isSubAdmin($this->UID))
		{
			$this->handleRequestSubAdmin($this->playlistsRepository, $parameters);
		}
		elseif ($this->aclValidator->isEditor($this->UID))
		{
			// Todo
		}
		elseif ($this->aclValidator->isViewer($this->UID))
		{
			// Todo
		}
		else
		{
			$this->handleRequestUser($this->playlistsRepository, $parameters);
		}

	}



}