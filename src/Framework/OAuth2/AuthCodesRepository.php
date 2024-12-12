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

use App\Framework\BaseRepositories\Sql;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodesRepository extends Sql implements AuthCodeRepositoryInterface
{
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
		$this->updateWithWhere(['revoked' => 1], ['token' => $codeId]);
	}

	/**
	 * @throws Exception
	 */
	public function isAuthCodeRevoked(string $codeId): bool
	{
		$revoked = (int) $this->findOneValueBy('revoked', ['token' => $codeId]);
		return $revoked === 1;
	}
}