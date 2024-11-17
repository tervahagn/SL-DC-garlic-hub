<?php

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
		echo "Command metadata written to $this->output_file\n";
	}
}
