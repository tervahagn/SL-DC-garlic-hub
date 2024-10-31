<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
	private $default_locale;

	public function __construct(string $default_locale)
	{
		$this->default_locale = $default_locale;
	}

	public function onKernelRequest(RequestEvent $event): void
	{
		$request = $event->getRequest();
		$locale  = $request->getSession()->get('_locale',  $this->default_locale);
		$request->setLocale($locale);
	}

	public static function getSubscribedEvents(): array
	{
		return [
			KernelEvents::REQUEST => [['onKernelRequest', 20]],
		];
	}
}
