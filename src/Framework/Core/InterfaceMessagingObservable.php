<?php
namespace App\Framework\Core;
/**
 * Interface InterfaceMessagingObservable
 */
interface InterfaceMessagingObservable
{
	/**
	 * register an observer
	 *
	 * @param InterfaceMessagingObserver $observer
	 * @return $this
	 */
	public function registerObserver(InterfaceMessagingObserver $observer): static;

	/**
	 * remove a registered observer by name
	 *
	 * @param   string  $observer_name
	 * @return  $this
	 */
	public function unregisterObserver($observer_name): static;

	/**
	 * send a message to all registered notifiers.
	 * Should call InterfaceMessagingObserver::notify($message) on every registered observer
	 *
	 * @param string    $message
	 * @return $this
	 */
	public function notifyObservers($message): static;
}