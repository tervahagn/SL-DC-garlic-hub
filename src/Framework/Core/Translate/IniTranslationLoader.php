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

namespace App\Framework\Core\Translate;

use RuntimeException;

class IniTranslationLoader implements TranslationLoaderInterface
{

	protected string $baseDirectory;

	public function __construct(string $baseDirectory)
	{
		$this->baseDirectory = rtrim($baseDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	}

	public function load(string $languageCode, string $module): array
	{
		$filePath = $this->buildFilePath($languageCode, $module);

		if (!file_exists($filePath))
			throw new RuntimeException("Translation file not found: $filePath");

		$data = parse_ini_file($filePath, true);

		if (!is_array($data))
			throw new RuntimeException("Invalid INI file format: $filePath");

		return $data;
	}

	protected function buildFilePath(string $languageCode, string $module): string
	{
		return $this->baseDirectory . $languageCode . '/' . $module . '.ini';
	}
}