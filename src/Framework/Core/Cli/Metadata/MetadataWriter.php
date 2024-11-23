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

namespace App\Framework\Core\Cli\Metadata;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;

/**
 * Writes command metadata to a JSON file.
 */
class MetadataWriter
{
	private string $output_file;
	private FilesystemOperator $filesystem;

	/**
	 * @param string $output_file Path to the output file.
	 */
	public function __construct(FilesystemOperator $filesystem, string $output_file)
	{
		$this->filesystem = $filesystem;
		$this->output_file = $output_file;
	}

	/**
	 * Writes command metadata to the output file in JSON format.
	 *
	 * @param array $commandData
	 *
	 * @return void
	 * @throws FilesystemException
	 */
	public function write(array $commandData): void
	{
		$jsonData = json_encode($commandData, JSON_PRETTY_PRINT);
		$this->filesystem->write($this->output_file, $jsonData);
	}
}
