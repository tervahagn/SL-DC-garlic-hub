<?php

namespace App\Framework\Core\Cli\Metadata;

/**
 * Writes command metadata to a JSON file.
 */
class MetadataWriter
{
	private string $output_file;

	/**
	 * @param string $output_file Path to the output file.
	 */
	public function __construct(string $output_file)
	{
		$this->output_file = $output_file;
	}

	/**
	 * Writes command metadata to the output file in JSON format.
	 *
	 * @param array $commandData Command metadata.
	 * @return void
	 */
	public function write(array $commandData): void
	{
		file_put_contents($this->output_file, json_encode($commandData, JSON_PRETTY_PRINT));
		echo "Command metadata written to $this->output_file\n";
	}
}
