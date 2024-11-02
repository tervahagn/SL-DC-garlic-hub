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

// src/Controller/LocaleController.php
namespace App\Controller;

use App\Services\LocaleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LocaleController extends AbstractController
{
	private LocaleService $localeService;

	public function __construct(LocaleService $localeService)
	{
		$this->localeService = $localeService;
	}

	/**
	 * @Route("/set_locale/{locale}", name="set_locales")
	 */
	public function setLocale(string $locale, Request $request): RedirectResponse
	{
		$this->localeService->setLocale($locale);
		return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('home'));
	}
}
