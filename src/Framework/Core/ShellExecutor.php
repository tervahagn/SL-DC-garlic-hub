<?php


namespace App\Framework\Core;

use App\Framework\Exceptions\CoreException;
use Psr\Log\LoggerInterface;

/**
 * Class ShellExecute
 */
class ShellExecutor
{
	private string $command = '';

	public function __construct() {}

	public function setCommand(string $command): static
	{
		$this->command = $command;
		return $this;
	}

	/**
	 * @throws CoreException
	 */
	public function execute(): array
	{
		$this->checkforCommand();

		$output = [];
		$returnCode = 0;
		exec($this->command . ' 2>&1', $output, $returnCode);

		if ($returnCode !== 0)
			throw new CoreException("Command failed: $this->command \n". 'output: '. implode("\n", $output));

		return ['output' => $output, 'code' => $returnCode];
	}

	/**
	 * @throws CoreException
	 */
	public function executeSimple(): string
	{
		$this->checkforCommand();
		$output = shell_exec($this->command . ' 2>&1');

		return $output;
	}

	private function checkforCommand()
	{
		if (empty($this->command))
			throw new CoreException('No command set for execution.');
	}

}