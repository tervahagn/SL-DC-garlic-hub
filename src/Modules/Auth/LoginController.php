<?php

namespace App\Modules\Auth;

use App\Framework\Exceptions\UserException;
use App\Framework\User\UserService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use SlimSession\Helper;

class LoginController
{
	private AuthService $authService;
	private LoggerInterface $logger;

	/**
	 * @param AuthService $authService
	 * @param LoggerInterface $logger
	 */
	public function __construct(AuthService $authService, LoggerInterface $logger)
	{
		$this->authService = $authService;
		$this->logger      = $logger;
	}

	public function showLogin(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$session  = $request->getAttribute('session');
		if ($session->exists('user'))
			return $this->redirect($response);

		return $this->renderForm($request, $response);
	}

	/**
	 * @throws UserException
	 */
	public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		try
		{
			$params   = (array) $request->getParsedBody();
			// no need to sanitize here, as we are executing prepared statements in DB
			$username = $params['username'] ?? null;
			$password = $params['password'] ?? null;

			$session  = $request->getAttribute('session');
			$flash    = $request->getAttribute('flash');

			$userEntity = $this->authService->login($username, $password);

			$session->set('locale', $userEntity->getMain()['locale']);
			$session->set('user', $userEntity->getMain());
		}
		catch (UserException $e)
		{
			$flash->addMessage('error', $e->getMessage());
			$this->logger->error($e->getMessage());
			return $this->redirect($response, '/login');
		}
		catch (Exception $e)
		{
			// Not tested because overengineered dbal bullshit exceptions make mocking a pain in ass
			$this->logger->error($e->getMessage());
		}
		catch (PhpfastcacheSimpleCacheException $e)
		{
			$this->logger->error($e->getMessage());
		}

		return $this->redirect($response);
	}

	public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$session = $request->getAttribute('session');
		$session->delete('user');
		return $this->redirect($response, '/login');
	}

	private function renderForm(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$flash    = $request->getAttribute('flash');
		$messages = $flash->getMessages(); // Flash-Nachrichten abholen
		$error	  = [];
		if (array_key_exists('error', $messages))
			$error = $messages['error'];

		$data = [
			'main_layout' => [
				'LANG_PAGE_TITLE' => 'Garlic Hub - Login',
				'error_messages' => $error,
				'ADDITIONAL_CSS' => ['/css/user/login.css']
			],
			'this_layout' => [
				'template' => 'auth/login', // Template-name
				'data' => [
					'LANG_PAGE_HEADER' => 'Login',
					'LANG_USERNAME' => 'Username / Email',
					'LANG_PASSWORD' => 'Password',
					'LANG_SUBMIT' => 'Login',
				]
			]
		];
		$response->getBody()->write(serialize($data));

		return $response->withHeader('Content-Type', 'text/html');
	}

	private function redirect(ResponseInterface $response, string $route = '/'): ResponseInterface
	{
		return $response->withHeader('Location', $route)->withStatus(302);
	}

}
