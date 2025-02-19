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
	private LoggerInterface $logger;

	/**
	 * @param LoggerInterface $logger
	 */
	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	public function setCommand(string $command): void
	{
		$this->command = $command;
	}

	/**
	 * @throws CoreException
	 */
	public function execute(): array
	{
		if (empty($this->command))
			throw new CoreException("No command set for execution.");

		$output = [];
		$returnCode = 0;
		exec($this->command . ' 2>&1', $output, $returnCode);

		if ($returnCode !== 0)
			$this->logger->error("Command failed: $this->command", ['output' => $output]);

		return ['output' => implode("\n", $output), 'code' => $returnCode];
	}
}