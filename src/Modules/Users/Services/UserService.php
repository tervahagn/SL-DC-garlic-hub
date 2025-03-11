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

namespace App\Modules\Users\Services;

use App\Modules\Users\Entities\UserEntity;
use App\Modules\Users\Entities\UserEntityFactory;
use App\Modules\Users\Repositories\Edge\UserMainRepository;
use App\Modules\Users\Repositories\UserRepositoryFactory;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Phpfastcache\Helper\Psr16Adapter;
use Psr\Cache\InvalidArgumentException;

/**
 * Userservice handles and caches the userEntity. This is a central point for authentication and checking
 *  user access rights for different modules.
 *
 * The class is user agnostic and the methods needs user id to work and return a userEntity.
 */
class UserService
{
	const int USER_STATUS_DELETED       = 0;
	const int USER_STATUS_LOCKED        = 1;
	const int USER_STATUS_REGISTERED    = 2;
	const int USER_STATUS_REGULAR       = 3;
	const int USER_STATUS_PREMIUM_A     = 4;
	const int USER_STATUS_PREMIUM_B     = 5;
	const int USER_STATUS_PREMIUM_C     = 6;
	const int USER_STATUS_PREMIUM_D     = 7;
	const int USER_STATUS_PREMIUM_E     = 8;
	const int USER_STATUS_ADMIN         = 9;

	private UserEntityFactory $userEntityFactory;
	private UserRepositoryFactory $userRepositoryFactory;
	private array $userRepositories;
	private Psr16Adapter $cache;

	public function __construct(UserRepositoryFactory $userRepositoryFactory, UserEntityFactory $userEntityFactory, Psr16Adapter
	$cache)
	{
		$this->userRepositoryFactory = $userRepositoryFactory;
		$this->userEntityFactory     = $userEntityFactory;
		$this->cache                 = $cache;
		$this->userRepositories      = $this->userRepositoryFactory->create();
	}

	public function getUserRepositories(): array
	{
		return $this->userRepositories;
	}

	public function updatePassword(int $UID, $password): int
	{
		$data = ['password' => password_hash($password, PASSWORD_DEFAULT)];

		return $this->updateUser($UID, $data);
	}

	public function updateUser(int $UID, array $data): int
	{
		return $this->getUserRepositories()['main']->update($UID, $data);
	}

	public function updateUserStats(int $UID, $sessionId): int
	{
		$data = [
			'login_time' => date('Y-m-d H:i:s'),
			'num_logins' => 'num_logins + 1',
			'session_id' => $sessionId,
		];

		return $this->getUserRepositories()['main']->update($UID, $data);
	}

	/**
	 * @throws Exception
	 */
	public function findUser($identifier): array
	{
		/** @var UserMainRepository $usrMainRepository */
		$usrMainRepository = $this->getUserRepositories()['main'];

		return $usrMainRepository->findByIdentifier($identifier);
	}

	/**
	 * Get the current user from cache or database.
	 *
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function getUserById(int $UID): UserEntity
	{
		$cacheKey   = $this->getCacheKey($UID);
		$cachedData = $this->cache->get($cacheKey);

		if ($cachedData)
			return $this->userEntityFactory->create($cachedData);

		$userData = $this->collectUserData($UID);

		// Cache the user data
		$this->cache->set($cacheKey, $userData, 3600 * 24); // Cache for 1 day

		return $this->userEntityFactory->create($userData);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \InvalidArgumentException
	 * @throws InvalidArgumentException
	 */
	public function invalidateCache(int $UID): void
	{
		$cacheKey = $this->getCacheKey($UID);
		$this->cache->delete($cacheKey);
	}

	/**
	 * @throws Exception
	 */
	private function collectUserData(int $UID): array
	{
		$userData = [];
		foreach ($this->userRepositories as $key => $repository)
		{
			$userData[$key] = $repository->findById($UID);
		}

		return $userData;
	}

	private function getCacheKey(int $UID): string
	{
		return "user_$UID";
	}
}