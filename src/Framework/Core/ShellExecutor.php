<?php


namespace App\Framework\Core;

use App\Framework\Exceptions\CoreException;

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
	 * @return array{output: array<string>, code: int}
	 * @throws CoreException
	 */
	public function execute(): array
	{
		$this->checkCommand();

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
		$this->checkCommand();
		$response = shell_exec($this->command . ' 2>&1');
		if ($response === false || $response === null)
			throw new CoreException("Command failed: $this->command");

		return $response;
	}

	/**
	 * @throws CoreException
	 */
	private function checkCommand(): void
	{
		if (empty($this->command))
			throw new CoreException('No command set for execution.');
	}

}