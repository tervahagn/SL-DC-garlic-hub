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
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository extends Sql implements RefreshTokenRepositoryInterface
{
	public function __construct(Connection $connection)
	{
		parent::__construct($connection,'oauth2_scopes', 'id');
	}

	public function getNewRefreshToken(): ?RefreshTokenEntityInterface
	{
		// TODO: Implement getNewRefreshToken() method.
	}

	/**
	 * @inheritDoc
	 */
	public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
	{
		// TODO: Implement persistNewRefreshToken() method.
	}

	public function revokeRefreshToken(string $tokenId): void
	{
		// TODO: Implement revokeRefreshToken() method.
	}

	public function isRefreshTokenRevoked(string $tokenId): bool
	{
		// TODO: Implement isRefreshTokenRevoked() method.
	}
}