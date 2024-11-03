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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * The event subscriber reads the locale setting from the session
 * for each HTTP request and applies it to the current request.
 */
class LocaleSubscriber implements EventSubscriberInterface
{
	/**
	 * @var RequestStack
	 */
	private RequestStack $requestStack;

	/**
	 * The RequestStack is injected into the subscriber via dependency injection, providing access
	 * to the current HTTP session and request.
	 *
	 * @param RequestStack $requestStack
	 */
	public function __construct(RequestStack $requestStack)
	{
		$this->requestStack = $requestStack;
	}

	/**
	 * @param RequestEvent $event
	 *
	 * @return void
	 */
	public function onKernelRequest(RequestEvent $event): void
	{
		$request = $event->getRequest();
		$session = $this->requestStack->getSession();

		if ($session->has('_locale'))
		{
			$request->setLocale($session->get('_locale'));
		}
	}

	/**
	 * @return array[]
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			KernelEvents::REQUEST => [['onKernelRequest', 20]],
		];
	}
}
