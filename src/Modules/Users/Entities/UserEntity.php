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

namespace App\Modules\Users\Entities;

use League\OAuth2\Server\Entities\UserEntityInterface;

/**
 * As we use the same entity with OAuth2, we need to implement the UserEntityInterface
 * from PhpLeage-Oauth2-Server
 */
class UserEntity implements UserEntityInterface
{
	private array $main;
	private array $contact;
	private array $stats;
	private array $security;
	private array $acl;
	private array $vip;

	public function __construct(array $main, array $contact, array $stats, array $security, array $acl, array $vip)
	{
		$this->main     = $main;
		$this->contact  = $contact;
		$this->stats    = $stats;
		$this->security = $security;
		$this->acl      = $acl;
		$this->vip      = $vip;
	}

	public function getMain(): array
	{
		return $this->main;
	}

	public function getContact(): array
	{
		return $this->contact;
	}

	public function getStats(): array
	{
		return $this->stats;
	}

	public function getSecurity(): array
	{
		return $this->security;
	}

	public function getAcl(): array
	{
		return $this->acl;
	}

	public function getVip(): array
	{
		return $this->vip;
	}

	public function getIdentifier(): string
	{
		return $this->main['id'];
	}
}
