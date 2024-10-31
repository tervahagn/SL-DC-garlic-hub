<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles requests to the home page and manages user locale settings.
 */
class Home extends AbstractController
{
	/**
	 * Renders the home page and sets the default locale in the session if not already set.
	 *
	 * @param Request $request The HTTP request object with session data.
	 * @return Response The rendered home page.
	 */
	#[Route('/', name: 'home')]
	public function home(Request $request): Response
	{
		if (!$request->getSession()->has('_locale'))
		{
			$request->getSession()->set('_locale', $this->getParameter('kernel.default_locale'));
		}
		return $this->render('base.html.twig', array());
	}

	/**
	 * Sets the user's preferred locale in the session and redirects to the home page.
	 *
	 * @param string $locale The locale code to set (e.g., "en" or "de").
	 * @param Request $request The HTTP request object with session data.
	 * @return Response A redirection to the home page.
	 */
	#[Route('/set-locale/{locale}', name: 'set_locale')]
	public function setLocale(string $locale, Request $request): Response
	{
		$request->getSession()->set('_locale', $locale);
		return $this->redirectToRoute('home');
	}
}
