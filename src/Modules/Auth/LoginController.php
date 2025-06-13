<?php

namespace App\Modules\Auth;

use App\Framework\Core\Cookie;
use App\Framework\Core\Session;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\UserException;
use Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginController
{
	private AuthService $authService;

	/**
	 * @param AuthService $authService
	 */
	public function __construct(AuthService $authService)
	{
		$this->authService = $authService;
	}

	/**
	 * @throws Exception|\Psr\SimpleCache\InvalidArgumentException
	 */
	public function showLogin(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$translator = $request->getAttribute('translator');
		$session    = $request->getAttribute('session');

		$csrfToken = bin2hex(random_bytes(32));
		$session->set('csrf_token', $csrfToken);
		$page_name = $translator->translate('login', 'login');
		$data = [
			'main_layout' => [
				'LANG_PAGE_TITLE' => $page_name,
				'additional_css' => ['/css/user/login.css']
			],
			'this_layout' => [
				'template' => 'auth/login', // Template-name
				'data' => [
					'LANG_PAGE_HEADER' => $page_name,
					'LANG_USERNAME' => $translator->translate('username', 'main').' / '. $translator->translate('email', 'main'),
					'LANG_PASSWORD' => $translator->translate('password', 'login'),
					'CSRF_TOKEN' => $csrfToken,
					'LANG_SUBMIT' => $page_name,
					'LANG_AUTOLOGIN' => $translator->translate('autologin', 'login')

				]
			]
		];
		$response->getBody()->write(serialize($data));

		return $response->withHeader('Content-Type', 'text/html');
	}


	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @return ResponseInterface
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws UserException
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		/** @var Session $session */
		$session  = $request->getAttribute('session');
		$params   = (array) $request->getParsedBody();
		$flash    = $request->getAttribute('flash');
		// no need to sanitize here, as we are executing prepared statements in DB
		$username = $params['username'] ?? null;
		$password = $params['password'] ?? null;

		$csrfToken = $params['csrf_token'] ?? null;

		if(!$session->exists('csrf_token') || $session->get('csrf_token') !== $csrfToken)
		{
			$flash->addMessage('error', 'Invalid CSRF token');
			return $this->redirect($response, '/login');
		}

		$userEntity = $this->authService->login($username, $password);
		if ($userEntity === null)
		{
			$flash->addMessage('error', $this->authService->getErrorMessage());
			return $this->redirect($response, '/login');
		}

		$main_data = $userEntity->getMain();
		$session->set('user', $main_data);
		$session->set('locale', $main_data['locale']);

		if (array_key_exists('autologin', $params))
		{
			$sessionId = Session::id();
			if ($sessionId === false)
			{
				$flash->addMessage('error', 'Bo session id found');
				return $this->redirect($response, '/login');
			}
			$this->authService->createAutologinCookie($main_data['UID'], $sessionId);
		}

		if (!$session->exists('oauth_redirect_params'))
			return $this->redirect($response);

		$oauthParams = $session->get('oauth_redirect_params');
		
		if (!is_array($oauthParams))
			return $this->redirect($response);

		$session->delete('oauth_redirect_params');
		return $this->redirect($response, '/api/authorize?' . http_build_query($oauthParams));
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		/** @var Session $session */
		$session = $request->getAttribute('session');
		$user    = $session->get('user');
		if (is_array($user))
			$this->authService->logout($user);

		$session->clear();
		$session->regenerateID();

		/** @var Cookie $cookie */
		$cookie = $request->getAttribute('cookie');
		$cookie->deleteCookie(AuthService::COOKIE_NAME_AUTO_LOGIN);

		return $this->redirect($response, '/login');
	}

	private function redirect(ResponseInterface $response, string $route = '/'): ResponseInterface
	{
		return $response->withHeader('Location', $route)->withStatus(302);
	}

}
