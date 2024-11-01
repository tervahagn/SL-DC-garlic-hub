<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sets the request locale based on session data or a default.
 */
class LocaleSubscriber implements EventSubscriberInterface
{
	/** @var string Default locale if none is set in session */
	private $default_locale;

	/**
	 * @param string $default_locale Default locale
	 */
	public function __construct(string $default_locale)
	{
		$this->default_locale = $default_locale;
	}

	/**
	 * Sets locale for the request from session or default.
	 *
	 * @param RequestEvent $event The request event
	 */
	public function onKernelRequest(RequestEvent $event): void
	{
		$request = $event->getRequest();
		$locale  = $request->getSession()->get('_locale', $this->default_locale);
		$request->setLocale($locale);
	}

	/**
	 * Subscribed events and priorities.
	 *
	 * @return array Event configuration
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			KernelEvents::REQUEST => [['onKernelRequest', 20]],
		];
	}
}
