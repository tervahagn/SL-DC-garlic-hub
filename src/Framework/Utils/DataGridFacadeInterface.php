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

namespace App\Framework\Utils;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;

/**
 * Interface for managing the behavior of a data grid.
 * Use this Facaade for OverviewControllers
 */
interface DataGridFacadeInterface
{
	public function configure(Translator $translator, Session $session): void;
	public function handleUserInput(array $userInputs): void;
	public function prepareDataGrid(): static;
	public function prepareTemplate(): array;
}