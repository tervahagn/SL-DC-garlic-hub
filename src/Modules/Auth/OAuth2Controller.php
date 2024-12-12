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

namespace App\Modules\Auth;

use App\Framework\OAuth2\OAuth2Service;
use App\Framework\User\UserEntity;
use Doctrine\DBAL\Exception;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class OAuth2Controller
{
	private AuthService $authService;
	private $session;
	private LoggerInterface $logger;
	private AuthorizationServer $authServer;

	/**
	 * @param AuthService $auth2Service
	 * @param LoggerInterface $logger
	 * @param AuthorizationServer $authServer
	 */
	public function __construct(AuthService $auth2Service, LoggerInterface $logger, AuthorizationServer $authServer)
	{
		$this->authService = $auth2Service;
		$this->logger = $logger;
		$this->authServer = $authServer;
	}


	public function token(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		try
		{
			// return a json token
			// {"access_token": "xy...z", "token_type": "Bearer", "expires_in": 3600, "scope": "read write"}
			return $this->authServer->respondToAccessTokenRequest($request, $response);
		}
		catch (OAuthServerException $e)
		{
			return $e->generateHttpResponse($response);
		}
		catch (\Exception $e)
		{
			$response->getBody()->write(json_encode(['error' => $e->getMessage()]));
			return $response->withStatus(500);
		}
	}

	public function authorize(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		try
		{
			$authRequest = $this->authServer->validateAuthorizationRequest($request);

			// check if user is logged in
			$this->session  = $request->getAttribute('session');
			if (!$this->session->exists('user'))
			{
				$params = $request->getQueryParams();
				$sanitizedParams = [
					'response_type' => $this->validateResponseType($params['response_type'] ?? null),
					'client_id'     => $this->validateClientId($params['client_id'] ?? null),
					'redirect_uri'  => $this->validateRedirectUri($params['redirect_uri'] ?? null),
					'state'         => $this->validateState($params['state'] ?? null),
				];
				$this->session->set('oauth_redirect_params', $sanitizedParams);
				return $response->withHeader('Location', '/login')->withStatus(302);
			}
			// Once the user has logged in set the user on the AuthorizationRequest
			$user = $this->session->get('user');
			$authRequest->setUser($this->authService->getCurrentUser($user['UID'])); // an instance of UserEntityInterface

			// At this point you should redirect the user to an authorization page.
			// This form will ask the user to approve the client and the scopes requested.

			// Once the user has approved or denied the client update the status
			// (true = approved, false = denied)
			$authRequest->setAuthorizationApproved(true);

			return $this->authServer->completeAuthorizationRequest($authRequest, $response);

		} catch (OAuthServerException $exception) {

			// All instances of OAuthServerException can be formatted into a HTTP response
			return $exception->generateHttpResponse($response);

		} catch (\Exception $exception) {

			// Unknown exception
			$body = new Stream(fopen('php://temp', 'r+'));
			$body->write($exception->getMessage());
			return $response->withStatus(500)->withBody($body);

		}
		catch (Exception $e) {
		}
	}

	public function confirmAccess(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$params = $request->getParsedBody();

		if (isset($params['approve']) && $params['approve'] === 'yes')
		{
			try {
				return $this->authorizationServer->completeAuthorizationRequest(
					AuthorizationRequest::fromSession($request), // Lade gespeicherte Anfrage
					$response
				);
			}
			catch (OAuthServerException $e)
			{
				return $e->generateHttpResponse($response);
			}
		}

		// Zugriff verweigert
		$response->getBody()->write(json_encode(['error' => 'access_denied']));
		return $response->withStatus(403);
	}

	private function isAccessConfirmed(ServerRequestInterface $request): bool
	{
		$session = $request->getAttribute('session');
		$UID = $session->get('user')['UID'];
		$clientId = $request->getQueryParams()['client_id'] ?? null;
	}



	private function validateResponseType(?string $responseType): string
	{
		$allowed = ['code', 'token']; // Beispielhaft erlaubte Werte
		if (!in_array($responseType, $allowed, true))
			throw new InvalidArgumentException('Invalid response_type');

		return $responseType;
	}

	private function validateClientId(?string $clientId): string
	{
		if (empty($clientId) || strlen($clientId) > 255)
			throw new InvalidArgumentException('Invalid client_id');

		return $clientId;
	}

	private function validateRedirectUri(?string $redirectUri): string
	{
		if (!filter_var($redirectUri, FILTER_VALIDATE_URL)) {
			throw new InvalidArgumentException('Invalid redirect_uri');
		}
		return $redirectUri;
	}

	private function validateState(?string $state): string
	{
		if (empty($state) || strlen($state) > 255) {
			throw new InvalidArgumentException('Invalid state');
		}
		return $state;
	}

}