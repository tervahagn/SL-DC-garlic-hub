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

namespace App\Framework\Services;

use App\Framework\Database\BaseRepositories\FilterBase;
use App\Framework\Utils\FormParameters\BaseParameters;
use Doctrine\DBAL\Exception;

abstract class AbstractDatatableService extends AbstractBaseService
{
	use SearchFilterParamsTrait;

	abstract public function loadDatatable(): void;

	/**
	 * @throws Exception
	 */
	protected function fetchForModuleAdmin(FilterBase $repository, BaseParameters $parameters): static
	{
		$total_elements 	   = $repository->countAllFiltered($parameters->getInputParametersArray());
		$results	           = $repository->findAllFiltered($parameters->getInputParametersArray());

		return $this->setAllResultData($total_elements,  $results);
	}

	/**
	 * @throws Exception
	 */
	protected function fetchForUser(FilterBase $repository, BaseParameters $parameters): static
	{
		$total_elements = $repository->countAllFilteredByUID($parameters->getInputParametersArray(), $this->UID);
		$results        = $repository->findAllFilteredByUID($parameters->getInputParametersArray(), $this->UID);

		return $this->setAllResultData($total_elements, $results);
	}
}