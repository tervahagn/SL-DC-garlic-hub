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

use App\Framework\Database\BaseRepositories\FilterBase;
use App\Framework\Services\AbstractBaseService;
use App\Modules\Profile\Entities\UserEntity;
use App\Modules\Profile\Entities\UserEntityFactory;
use App\Modules\Users\Repositories\Edge\UserMainRepository;
use App\Modules\Users\Repositories\UserRepositoryFactory;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Phpfastcache\Helper\Psr16Adapter;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * User service handles and caches the userEntity. This is a central point for authentication and checking
 *  user access rights for different modules.
 *
 * The class is user agnostic and the methods needs user id to work and return a userEntity.
 */
class UsersService extends AbstractBaseService
{
	const int USER_STATUS_DELETED       = 0;
	const int USER_STATUS_LOCKED        = 1;
	const int USER_STATUS_REGISTERED    = 2;
	const int USER_STATUS_REGULAR       = 3;
/*	const int USER_STATUS_PREMIUM_A     = 4;
	const int USER_STATUS_PREMIUM_B     = 5;
	const int USER_STATUS_PREMIUM_C     = 6;
	const int USER_STATUS_PREMIUM_D     = 7;
	const int USER_STATUS_PREMIUM_E     = 8;
	const int USER_STATUS_ADMIN         = 9;
*/
	private UserEntityFactory $userEntityFactory;
	private UserRepositoryFactory $userRepositoryFactory;
	private Psr16Adapter $cache;
	/** @var array<string,FilterBase>  */
	private array $userRepositories;

	public function __construct(UserRepositoryFactory $userRepositoryFactory, UserEntityFactory $userEntityFactory, Psr16Adapter
	$cache, LoggerInterface $logger)
	{
		$this->userRepositoryFactory = $userRepositoryFactory;
		$this->userEntityFactory     = $userEntityFactory;
		$this->cache                 = $cache;
		$this->userRepositories      = $this->userRepositoryFactory->create();
		parent::__construct($logger);
	}

	/**
	 * @return array<string,FilterBase>
	 */
	public function getUserRepositories(): array
	{
		return $this->userRepositories;
	}

	/**
	 * @return array<string,mixed>
	 * @throws Exception
	 */
	public function loadForEdit(int $UID): array
	{
		return $this->getUserRepositories()['main']->findById($UID);
	}

	/**
	 * @throws Exception
	 */
	public function updatePassword(int $UID, string $password): int
	{
		$data = ['password' => password_hash($password, PASSWORD_DEFAULT)];

		return $this->getUserRepositories()['main']->update($UID, $data);
	}

	/**
	 * @param array<string,string> $post
	 * @throws Exception
	 */
	public function inserNewUser(array $post): int
	{
		if (!$this->isUnique(0, $post['username'], $post['email']))
			return 0;

		$saveData = $this->collectCommonData($post, []);

		return (int) $this->getUserRepositories()['main']->insert($saveData);
	}

	/**
	 * @param array<string,string> $post
	 * @throws Exception
	 */
	public function updateUser(int $UID, array $post): int
	{
		if (!$this->isUnique($UID, $post['username'], $post['email']))
			return 0;

		$saveData = $this->collectCommonData($post, []);

		return $this->getUserRepositories()['main']->update($UID, $saveData);
	}

	/**
	 * @throws Exception
	 */
	public function updateUserStats(int $UID, string $sessionId): int
	{
		$data = [
			'login_time' => date('Y-m-d H:i:s'),
			'num_logins' => 'num_logins + 1',
			'session_id' => $sessionId,
		];

		return $this->getUserRepositories()['main']->update($UID, $data);
	}

	/**
	 * @return array<string,mixed>
	 * @throws Exception
	 */
	public function findUser(string $identifier): array
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
	 * @param array<string,mixed> $postData
	 * @param array<string,mixed> $saveData
	 * @return array<string,mixed>
	 */
	private function collectCommonData(array $postData, array $saveData): array
	{
		if (isset($postData['username']))
			$saveData['username'] = $postData['username'];

		if (isset($postData['email']))
			$saveData['email'] = $postData['email'];

		if (isset($postData['locale']))
			$saveData['locale'] = $postData['locale'];

		if (isset($postData['status']))
			$saveData['status'] = $postData['status'];

		return $saveData;
	}

	/**
	 * @throws Exception
	 */
	private function isUnique(int $UID, string $username, string $email): bool
	{
		$where = [
			'username' => $this->getUserRepositories()['main']->generateWhereClause($username, '=', 'OR'),
			'email' => $this->getUserRepositories()['main']->generateWhereClause($email, '=', 'OR')
		];
		$result =  $this->getUserRepositories()['main']->findAllByWithFields(['UID', 'username', 'email'], $where);

		if (empty($result))
			return true;

		foreach ($result as $existing)
		{
			if ($existing['username'] === $username && (int) $existing['UID'] !== $UID)
				$this->addErrorMessage('username_exists');

			if ($existing['email'] === $email && (int) $existing['UID'] !== $UID)
				$this->addErrorMessage('email_exists');
		}

		if (!$this->hasErrorMessages())
			return true;

		return false;
		

	}


	/**
	 * @return array<string,mixed>
	 * @throws Exception
	 */
	private function collectUserData(int $UID): array
	{
		$userData = [];
		/** @var FilterBase $repository */
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