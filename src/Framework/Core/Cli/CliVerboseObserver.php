<?php
namespace App\Framework\Core\Cli;

use App\Framework\Core\InterfaceMessagingObserver;

class CliVerboseObserver implements InterfaceMessagingObserver
{
	/**
	 * @param 	string	$message
	 * @return 	$this
	 */
	public function notify($message): static
	{
		print $message . PHP_EOL;
		return $this;
	}
}