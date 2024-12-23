<?php

namespace App\Modules\Auth;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\UserException;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use SlimSession\Helper;

class LoginController
{
	private AuthService $authService;
	private LoggerInterface $logger;
	private Translator $translator;

	/**
	 * @param AuthService $authService
	 * @param LoggerInterface $logger
	 */
	public function __construct(AuthService $authService, LoggerInterface $logger)
	{
		$this->authService = $authService;
		$this->logger      = $logger;
	}

	/**
	 * @throws \Exception|\Psr\SimpleCache\InvalidArgumentException
	 */
	public function showLogin(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$this->translator = $request->getAttribute('translator');
		$session          = $request->getAttribute('session');
		if ($session->exists('user'))
			return $this->redirect($response);

		$csrfToken = bin2hex(random_bytes(32));
		$session->set('csrf_token', $csrfToken);
		$page_name = $this->translator->translate('login', 'login');
		$data = [
			'main_layout' => [
				'LANG_PAGE_TITLE' => $page_name,
				'additional_css' => ['/css/user/login.css']
			],
			'this_layout' => [
				'template' => 'auth/login', // Template-name
				'data' => [
					'LANG_PAGE_HEADER' => $page_name,
					'LANG_USERNAME' => $this->translator->translate('username', 'main').' / '.$this->translator->translate('email', 'main'),
					'LANG_PASSWORD' => $this->translator->translate('password', 'login'),
					'CSRF_TOKEN' => $csrfToken,
					'LANG_SUBMIT' => $page_name,
					'LANG_AUTOLOGIN' => $this->translator->translate('autologin', 'login')

				]
			]
		];
		$response->getBody()->write(serialize($data));

		return $response->withHeader('Content-Type', 'text/html');
	}

	/**
	 * @throws UserException
	 */
	public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		/** @var Helper $session */
		$session  = $request->getAttribute('session');
		try
		{
			$params   = (array) $request->getParsedBody();
			// no need to sanitize here, as we are executing prepared statements in DB
			$username = $params['username'] ?? null;
			$password = $params['password'] ?? null;

			$csrfToken = $params['csrf_token'] ?? null;
			if(!$session->exists('csrf_token') || $session->get('csrf_token') !== $csrfToken)
				throw new UserException('CSRF Token mismatch');

			$userEntity = $this->authService->login($username, $password);
			$main_data = $userEntity->getMain();
			$session->set('user', $main_data);
			$session->set('locale', $main_data['locale']);
		}
		catch (\Exception | InvalidArgumentException | Exception $e)
		{
			// dbal exception not tested because overengineered bullshit make mocking a pain in ass
			$flash  = $request->getAttribute('flash');
			$flash->addMessage('error', $e->getMessage());
			$this->logger->error($e->getMessage());
			return $this->redirect($response, '/login');
		}

		if (!$session->exists('oauth_redirect_params'))
			return $this->redirect($response);

		$oauthParams = $session->get('oauth_redirect_params', []);
		$session->delete('oauth_redirect_params');

		return $this->redirect($response, '/api/authorize?' . http_build_query($oauthParams));
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$session = $request->getAttribute('session');
		$user    = $session->get('user');
		$this->authService->logout($user);
		$session->delete('user');
		return $this->redirect($response, '/login');
	}

	private function redirect(ResponseInterface $response, string $route = '/'): ResponseInterface
	{
		return $response->withHeader('Location', $route)->withStatus(302);
	}

}
