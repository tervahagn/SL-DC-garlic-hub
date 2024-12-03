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

namespace App\Framework\User;

use App\Framework\Core\Config\Config;
use Doctrine\DBAL\Connection;

class UserRepositoryFactory
{
	private Config $config;
	private Connection $connection;

	/**
	 * @param Config $config
	 */
	public function __construct(Config $config, Connection $connection)
	{
		$this->config = $config;
		$this->connection = $connection;
	}

	public function create(): array
	{
		switch ($this->config->getEdition())
		{
			case Config::PLATFORM_EDITION_ENTERPRISE:
				return [
					'main'     => new Edge\UserMainRepository($this->connection),
					'acl'      => new Core\UserAclRepository($this->connection),
					'contact'  => new Core\UserContactRepository($this->connection),
					'stats'    => new Core\UserStatsRepository($this->connection),
					'vip'      => new Enterprise\UserVipRepository($this->connection),
					'security' => new Enterprise\UserSecurityRepository($this->connection)
				];
			case Config::PLATFORM_EDITION_CORE:
				return [
					'main'     => new Edge\UserMainRepository($this->connection),
					'acl'      => new Core\UserAclRepository($this->connection),
					'contact'  => new Core\UserContactRepository($this->connection),
					'stats'    => new Core\UserStatsRepository($this->connection)
				];
			case Config::PLATFORM_EDITION_EDGE:
			default:
				return [
					'main'     => new Edge\UserMainRepository($this->connection)
				];
		}
	}


}