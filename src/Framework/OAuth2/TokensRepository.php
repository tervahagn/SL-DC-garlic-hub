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

use App\Framework\Database\BaseRepositories\SqlBase;
use App\Framework\Database\BaseRepositories\Traits\CrudTraits;
use App\Framework\Database\BaseRepositories\Traits\FindOperationsTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class TokensRepository extends SqlBase implements AuthCodeRepositoryInterface, AccessTokenRepositoryInterface, RefreshTokenRepositoryInterface
{
	use CrudTraits, FindOperationsTrait;

	public function __construct(Connection $connection)
	{
		parent::__construct($connection,'oauth2_credentials', 'id');
	}

	public function getNewAuthCode(): AuthCodeEntityInterface
	{
		return new AuthCodeEntity();
	}

	/**
	 * @throws Exception
	 */
	public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
	{
		$data = [
			'type'               => 'auth_code',
			'token'              => $authCodeEntity->getIdentifier(),
			'client_id'          => $authCodeEntity->getClient()->getIdentifier(),
			'UID'                => $authCodeEntity->getUserIdentifier(),
			'redirect_uri'       => $authCodeEntity->getRedirectUri(),
			'scopes'             => implode(' ', $authCodeEntity->getScopes()),
			'expires_at'         => $authCodeEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),
			'created_at'         => date('Y-m-d H:i:s')
		];

		$this->insert($data);
	}

	/**
	 * @throws Exception
	 */
	public function revokeAuthCode(string $codeId): void
	{
		$this->updateWithWhere(['revoked' => 1], ['token' => $codeId, 'type' => 'auth_code']);
	}

	/**
	 * @throws Exception
	 */
	public function isAuthCodeRevoked(string $codeId): bool
	{
		$revoked = (int) $this->findOneValueBy('revoked', ['token' => $codeId, 'type' => 'auth_code']);
		return $revoked === 1;
	}

	public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, ?string $userIdentifier = null): AccessTokenEntityInterface
	{
		$accessToken = new AccessTokenEntity();
		$accessToken->setClient($clientEntity);
		$accessToken->setUserIdentifier($userIdentifier);
		$accessToken->addScope(new ScopeEntity());

		return $accessToken;
	}

	/**
	 * @throws Exception
	 */
	public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
	{
		$data = [
			'type'       => 'access_token',
			'token'      => $accessTokenEntity->getIdentifier(),
			'client_id'  => $accessTokenEntity->getClient()->getIdentifier(),
			'UID'        => $accessTokenEntity->getUserIdentifier(),
			'scopes'     => implode(' ', array_map(fn($scope) => $scope->getIdentifier(), $accessTokenEntity->getScopes())),
			'expires_at' => $accessTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),
			'created_at' => date('Y-m-d H:i:s')
		];

		$this->insert($data);
	}

	/**
	 * @throws Exception
	 */
	public function revokeAccessToken(string $tokenId): void
	{
		$this->updateWithWhere(['revoked' => 1], ['token' => $tokenId]);
	}

	/**
	 * @throws Exception
	 */
	public function isAccessTokenRevoked(string $tokenId): bool
	{
		$revoked = (int) $this->findOneValueBy('revoked', ['token' => $tokenId, 'type' => 'access_token']);
		return $revoked === 1;
	}

	public function getNewRefreshToken(): ?RefreshTokenEntityInterface
	{
		return new RefreshTokenEntity();
	}

	/**
	 * @throws Exception
	 */
	public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
	{
		$data = [
			'type'       => 'refresh_token',
			'token'      => $refreshTokenEntity->getIdentifier(),
			'client_id'  => $refreshTokenEntity->getAccessToken()->getClient()->getIdentifier(),
			'UID'        => $refreshTokenEntity->getAccessToken()->getUserIdentifier(),
			'scopes'     => implode(' ', array_map(fn($scope) => $scope->getIdentifier(), $refreshTokenEntity->getAccessToken()->getScopes())),
			'expires_at' => $refreshTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),
			'created_at' => date('Y-m-d H:i:s')
		];

		$this->insert($data);
	}

	/**
	 * @throws Exception
	 */
	public function revokeRefreshToken(string $tokenId): void
	{
		$this->updateWithWhere(['revoked' => 1], ['token' => $tokenId]);
	}

	/**
	 * @throws Exception
	 */
	public function isRefreshTokenRevoked(string $tokenId): bool
	{
		$revoked = (int) $this->findOneValueBy('revoked', ['token' => $tokenId, 'type' => 'refresh_token']);
		return $revoked === 1;
	}
}