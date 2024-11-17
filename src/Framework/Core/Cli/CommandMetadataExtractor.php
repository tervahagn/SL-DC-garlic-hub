<?php

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
