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

namespace App\Framework\OAuth2;

use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\UserException;
use Doctrine\DBAL\Exception;

class OAuth2Service
{
	private ClientsRepository $clientRepository;
	private TokensRepository $tokensRepository;
	private AuthCodesRepository $authorizationCodesRepository;

	/**
	 * @param ClientsRepository $clientRepository
	 * @param TokensRepository $tokensRepository
	 * @param AuthCodesRepository $authorizationCodesRepository
	 */
	public function __construct(ClientsRepository $clientRepository, TokensRepository $tokensRepository, AuthCodesRepository $authorizationCodesRepository)
	{
		$this->clientRepository = $clientRepository;
		$this->tokensRepository = $tokensRepository;
		$this->authorizationCodesRepository = $authorizationCodesRepository;
	}

	/**
	 * @throws FrameworkException
	 * @throws Exception
	 */
	public function handleAuthorization(string $clientId, string $clientSecret, string $redirectUri): string
	{
		$this->clientRepository->validateClient($clientId, $clientSecret);
		if (empty($client))
			throw new FrameworkException('No client found');

		if ($redirectUri !== $client['redirect_uri'])
			throw new FrameworkException('Invalid redirect uri.');

		if (!password_verify($clientSecret, $client['client_secrets']))
			throw new FrameworkException('Invalid credentials.');

		return $client['redirect_uri'];
	}

	public function exchangeToken(string $authCode, string $clientId, string $clientSecret): string
	{
		// Logik zum Austausch eines Authorization Codes gegen einen Access Token
	}

	public function validateToken(string $accessToken): bool
	{
		// Logik zur Validierung eines Access Tokens
	}

}