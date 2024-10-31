<?php

namespace App\Modules\Auth\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
	private UrlGeneratorInterface $urlGenerator;
	private CsrfTokenManagerInterface $csrfTokenManager;

	public function __construct(UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager)
	{
		$this->urlGenerator = $urlGenerator;
		$this->csrfTokenManager = $csrfTokenManager;
	}

	public function authenticate(Request $request): Passport
	{
		$username = $request->request->get('username', '');
		$password = $request->request->get('password', '');
		$csrfToken = $request->request->get('_csrf_token');

		// CSRF-Token-Überprüfung
		if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('authenticate', $csrfToken))) {
			throw new InvalidCsrfTokenException();
		}

		return new Passport(
			new UserBadge($username),
			new PasswordCredentials($password),
			[]
		);
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
	{
		return new RedirectResponse($this->urlGenerator->generate('player')); // Route für Dashboard
	}

	protected function getLoginUrl(Request $request): string
	{
		return $this->urlGenerator->generate('app_login'); // Route für das Login-Formular
	}
}
