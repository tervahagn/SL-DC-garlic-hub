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

namespace App\Framework\Utils\FormParameters;

use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Exceptions\ModuleException;

interface BaseFilterParametersInterface
{
	public const string PARAMETER_ELEMENTS_PER_PAGE = 'elements_per_page';
	public const string PARAMETER_ELEMENTS_PAGE     = 'elements_page';
	public const string PARAMETER_SORT_COLUMN       = 'sort_column';
	public const string PARAMETER_SORT_ORDER        = 'sort_order';
	const string PARAMETER_COMPANY_ID               = 'company_id';

	public function setParameterDefaultValues($default_sort_column): static;

	public function setElementsParametersToNull(): static;

	public function parseInputFilterAllUsers(): static;

	public function hasSessionKeyStore(): bool;

	public function addCompany(): void;

}