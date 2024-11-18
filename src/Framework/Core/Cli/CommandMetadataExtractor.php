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

namespace App\Framework\Core\Cli;

use App\Framework\Core\Cli\Metadata\MetadataWriter;

/**
 * Extracts command metadata from PHP files in a directory.
 */
class CommandMetadataExtractor
{

	/**
	 * @param string $commandsDir Path to the directory containing PHP files.
	 *
	 * @return array
	 */
	public function extract(string $commandsDir): array
	{
		$commandData = [];
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($commandsDir, \FilesystemIterator::SKIP_DOTS)
		);

		foreach ($iterator as $file)
		{
			if ($file->isFile() && $file->getExtension() === 'php')
			{
				$metadata = $this->extractMetadata($file->getPathname());

				if (!isset($metadata['command']))
					continue;

				$metadata['filepath'] = $file->getPathname();
				$commandData[$metadata['command']] = $metadata;
			}
		}

		return $commandData;
	}

	/**
	 * @param string $file_path Path to the PHP file.
	 * @return array Command metadata.
	 */
	private function extractMetadata(string $file_path): array
	{
		$cli_meta = [];

		try {
			set_error_handler(function($errno, $errstr, $errfile, $errline)
			{
				echo "Error in file $errfile (Line $errline): $errstr\n";
				return true; // Suppress errors
			});

			include $file_path;

		}
		catch (\Throwable $e)
		{
			echo "Error extracting metadata: " . $e->getMessage() . "\n";
		}

		return $cli_meta;
	}
}
