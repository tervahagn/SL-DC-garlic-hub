<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

namespace App\Framework\Core\Config;

use App\Framework\Exceptions\CoreException;

interface ConfigLoaderInterface
{
	/**
	 * Loads the configuration for a specific module.
	 *
	 * This method retrieves configuration data from a file based on the
	 * given module name. The data is cached to avoid redundant file reads.
	 *
	 * @param string $module The name of the module to load the configuration for.
	 * @return array The configuration data as an associative array.
	 * @throws CoreException If the configuration file is missing or invalid.
	 */
	public function loadConfig(string $module): array;
}