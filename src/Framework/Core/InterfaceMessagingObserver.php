<?php
namespace App\Framework\Core;

/**
 * Interface InterfaceMessagingObserver
 */
interface InterfaceMessagingObserver
{
	/**
	 * sends a message to current observer
	 *
	 * @param string    $message
	 * @return $this
	 */
	public function notify($message): static;
}
