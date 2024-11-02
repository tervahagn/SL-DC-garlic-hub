<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


namespace App\EventSubscriber;

use App\Services\LocaleService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscriber that sets the locale for each request based on session data.
 *
 * This subscriber listens to the `KernelEvents::REQUEST` event and sets the request's locale
 * according to the session's stored locale, ensuring consistent language settings for the user.
 */
class LocaleSubscriber implements EventSubscriberInterface
{
	/**
	 * Service for managing locale settings.
	 *
	 * @var LocaleService
	 */
	private LocaleService $localeService;

	/**
	 * LocaleSubscriber constructor.
	 *
	 * @param LocaleService $localeService The locale service for retrieving the current session locale.
	 */
	public function __construct(LocaleService $localeService)
	{
		$this->localeService = $localeService;
	}

	/**
	 * Event handler for the kernel request event.
	 *
	 * Sets the locale on the request object based on the session's current locale.
	 *
	 * @param RequestEvent $event The current request event.
	 *
	 * @return void
	 */
	public function onKernelRequest(RequestEvent $event): void
	{
		$request = $event->getRequest();
		$locale  = $this->localeService->getCurrentLocale();
		$request->setLocale($locale);
	}

	/**
	 * Registers the event listener for the `KernelEvents::REQUEST` event.
	 *
	 * The priority is set to 20, so this subscriber runs early in the request lifecycle.
	 *
	 * @return array<string, array<int, mixed>>  array of subscribed events and corresponding methods.
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			KernelEvents::REQUEST => [['onKernelRequest', 20]],
		];
	}
}
