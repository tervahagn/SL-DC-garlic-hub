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

namespace App\Modules\Mediapool\Utils;

use App\Framework\Exceptions\ModuleException;
use finfo;
use InvalidArgumentException;

class MimeTypeDetector
{
	private finfo $finfo;

	public function __construct()
	{
		$this->finfo = finfo_open(FILEINFO_MIME_TYPE);
	}

	public function __destruct()
	{
		if (isset($this->finfo)) // needed because of the tests
			finfo_close($this->finfo);
	}

	/**
	 * @throws ModuleException
	 */
	public function detectFromFile(string $filePath): string
	{
		if (!file_exists($filePath))
			throw new InvalidArgumentException("File '$filePath' not exists.");


		// exception for the digital signage widgets
		if (pathinfo($filePath, PATHINFO_EXTENSION) === 'wgt')
			return 'application/widget';

		$mimeType = finfo_file($this->finfo, $filePath);
		if ($mimeType === false)
			throw new ModuleException('mediapool', "MIME-Type for '$filePath' could not be detected.");


		return $mimeType;
	}

	/**
	 * @throws ModuleException
	 */
	public function detectFromStream($stream): string
	{
		if (!is_resource($stream) || get_resource_type($stream) !== 'stream')
			throw new InvalidArgumentException('Invalid stream.');

		$content = stream_get_contents($stream, -1, 0);
		if ($content === false)
			throw new ModuleException('mediapool','Stream was not readable');


		$mimeType = finfo_buffer($this->finfo, $content);
		if ($mimeType === false)
			throw new ModuleException('mediapool', 'MIME-Type could not be detected from stream');

		return $mimeType;
	}
}