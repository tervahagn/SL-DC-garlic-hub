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

use _PHPStan_e6dc705b2\Nette\InvalidArgumentException;
use App\Framework\OAuth2\OAuth2Service;
use Doctrine\DBAL\Exception;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class OAuth2Controller
{
	private AuthService $auth2Service;
	private OAuth2Service $oauth2Service;
	private LoggerInterface $logger;
	private AuthorizationServer $authServer;

	/**
	 * @param AuthService $auth2Service
	 * @param OAuth2Service $oauth2Service
	 * @param LoggerInterface $logger
	 * @param AuthorizationServer $authServer
	 */
	public function __construct(AuthService $auth2Service, OAuth2Service $oauth2Service, LoggerInterface $logger, AuthorizationServer $authServer)
	{
		$this->auth2Service = $auth2Service;
		$this->oauth2Service = $oauth2Service;
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
		// 1.check if user is logged in
		if (!$this->getAuthenticatedUser($request))
		{
			$session = $request->getAttribute('session');
			$params = $request->getQueryParams();
			$sanitizedParams = [
				'response_type' => $this->validateResponseType($params['response_type'] ?? null),
				'client_id'     => $this->validateClientId($params['client_id'] ?? null),
				'redirect_uri'  => $this->validateRedirectUri($params['redirect_uri'] ?? null),
				'scope'         => $this->validateScope($params['scope'] ?? null),
				'state'         => $this->validateState($params['state'] ?? null),
			];
			$session->set('oauth_redirect_params', $sanitizedParams);
			return $response->withHeader('Location', '/login')->withStatus(302);
		}


		// 2. Zugriff des Clients bestätigen (Benutzer-UI für Zustimmung)
		if (!$this->isAccessConfirmed($request))
		{
			return $response->withHeader('Location', '/confirm-access')->withStatus(302);
		}

		try
		{
			// 3. Authorization Request verarbeiten
			$authRequest = $this->authorizationServer->validateAuthorizationRequest($request);

			// 4. Benutzer dem Auth-Request hinzufügen
			$authRequest->setUser(new UserEntity($user->getId()));
			$authRequest->setAuthorizationApproved(true); // Zugriff wurde bestätigt

			// 5. Antwort mit Autorisierungscode zurückgeben
			return $this->authorizationServer->completeAuthorizationRequest($authRequest, $response);
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

		// Prüfen, ob in der Datenbank eine Zustimmung existiert
		return $this->database->fetchOne(
				"SELECT 1 FROM user_client_consent WHERE user_id = ? AND client_id = ?",
				[$userId, $clientId]
			) !== false;
	}

	private function getAuthenticatedUser(ServerRequestInterface $request): bool
	{
		$session = $request->getAttribute('session');
		return $session->exist('user');
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

	private function validateScope(?string $scope): string
	{
		// Beispiel: Scope-Liste aufteilen und prüfen
		$allowedScopes = ['read', 'write', 'delete']; // Beispielhaft erlaubte Scopes
		$scopes = explode(' ', $scope ?? '');
		foreach ($scopes as $s) {
			if (!in_array($s, $allowedScopes, true)) {
				throw new InvalidArgumentException('Invalid scope: ' . $s);
			}
		}
		return $scope;
	}

	private function validateState(?string $state): string
	{
		if (empty($state) || strlen($state) > 255) {
			throw new InvalidArgumentException('Invalid state');
		}
		return $state;
	}

}