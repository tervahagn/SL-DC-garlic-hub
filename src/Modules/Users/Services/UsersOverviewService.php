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

use App\Framework\Database\BaseRepositories\FilterBase;
use App\Framework\Services\AbstractBaseService;
use App\Framework\Utils\FormParameters\BaseParameters;
use App\Framework\Utils\FormParameters\Traits\SearchFilterParams;
use App\Modules\Users\Helper\Overview\Parameters;
use App\Modules\Users\Repositories\Edge\UserMainRepository;
use Psr\Log\LoggerInterface;

class UsersOverviewService extends AbstractBaseService
{
	use SearchFilterParams;
	private readonly UserMainRepository $userMainRepository;
	private readonly AclValidator $aclValidator;

	public function __construct(UserMainRepository $userMainRepository, AclValidator $aclValidator,  LoggerInterface $logger)
	{
		$this->userMainRepository = $userMainRepository;
		$this->aclValidator = $aclValidator;
		parent::__construct($logger);
	}

	public function loadUsersForOverview(Parameters $parameters): void
	{
		if ($this->aclValidator->isModuleAdmin($this->UID))
		{
			$this->handleRequestModuleAdmin($this->userMainRepository, $parameters);
		}
		elseif ($this->aclValidator->isSubAdmin($this->UID))
		{
			$this->handleRequestSubAdmin($this->userMainRepository, $parameters);
		}
	}

	public function handleRequestModuleAdmin(FilterBase $repository, BaseParameters $parameters): static
	{
		// later		$this->setCompanyArray($this->getUser()->getAllCompanyIds());
		// for edge
		$this->setCompanyArray([[1 => 'local']]);

		$this->setAllowedCompanyIds(array_keys($this->getCompanyArray()));

		$total_elements 	   = $repository->countAllFiltered($parameters->getInputParametersArray());
		$results	           = $repository->findAllFiltered($parameters->getInputParametersArray());

		return $this->setAllResultData($total_elements,  $results);
	}

	public function handleRequestSubAdmin(FilterBase $repository, BaseParameters $parameters): static
	{
		// companies to show names in dropdowns e.g.
		$this->setCompanyArray($this->getUser()->getAllCompanyIds());

		$company_ids = $this->aclValidator->determineCompaniesForSubAdmin();
		$this->setAllowedCompanyIds($company_ids);

		$total_elements = $repository->countAllFilteredByUIDCompanyReseller(
			$company_ids,
			$parameters->getInputParametersArray(),
			$this->getUser()->getUID()
		);

		$results = $repository->findAllFilteredByUIDCompanyReseller(
			$company_ids,
			$parameters->getInputParametersArray(),
			$this->getUser()->getUID()
		);
		return $this->setAllResultData($total_elements,  $results);
	}
}