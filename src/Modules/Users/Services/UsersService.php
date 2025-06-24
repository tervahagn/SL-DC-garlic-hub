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
use App\Framework\Exceptions\ModuleException;
use App\Framework\Services\AbstractBaseService;
use App\Modules\Profile\Entities\TokenPurposes;
use App\Modules\Profile\Entities\UserEntity;
use App\Modules\Profile\Entities\UserEntityFactory;
use App\Modules\Profile\Services\UserTokenService;
use App\Modules\Users\Repositories\Edge\UserAclRepository;
use App\Modules\Users\Repositories\Edge\UserMainRepository;
use App\Modules\Users\Repositories\Edge\UserTokensRepository;
use App\Modules\Users\Repositories\UserRepositoryFactory;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Phpfastcache\Helper\Psr16Adapter;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Throwable;

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
	private UserTokenService $userTokenService;
	private Psr16Adapter $cache;
	/** @var array{
	 *     main: UserMainRepository,
	 *     tokens: UserTokensRepository,
	 *     acl: UserAclRepository
	 *	 }  */
	private array $userRepositories;

	public function __construct(UserRepositoryFactory $userRepositoryFactory, UserEntityFactory $userEntityFactory, UserTokenService $userTokenService, Psr16Adapter $cache, LoggerInterface $logger)
	{
		$this->userRepositoryFactory = $userRepositoryFactory;
		$this->userEntityFactory     = $userEntityFactory;
		$this->cache                 = $cache;
		$this->userRepositories      = $this->userRepositoryFactory->create();
		$this->userTokenService      = $userTokenService;
		parent::__construct($logger);
	}

	/**
	 * @return array<string,mixed>
	 * @throws Exception
	 * @throws ModuleException
	 */
	public function loadForAdminEdit(int $UID): array
	{
		$user = $this->userRepositories['main']->findByIdSecured($UID);
		if (empty($user))
			throw new ModuleException('users', 'User not found');

		$user['tokens'] = $this->loadUserTokensForAdminEdit($UID);
		return $user;
	}

	/**
	 * @return list<array{token:string, UID: int, purpose: string, expires_at: string, used_at:string,null}>
	 * @throws Exception
	 */
	public function loadUserTokensForAdminEdit(int $UID): array
	{
		return $this->userTokenService->findTokenByUID($UID);
	}

	/**
	 * @param array<string,string> $post
	 * @throws Exception
	 */
	public function insertNewUser(array $post): int
	{
		try
		{
			$this->userRepositories['main']->beginTransaction();
			if (!$this->isUnique(0, $post['username'], $post['email']))
				throw new ModuleException('users', 'Values are not unique');

			$saveData = $this->collectCommonData($post, []);

			$UID = (int) $this->userRepositories['main']->insert($saveData);
			if ($UID === 0)
				throw new ModuleException('users', 'insert failed.');

			$this->userTokenService->insertToken($UID, TokenPurposes::INITIAL_PASSWORD);

			$this->userRepositories['main']->commitTransaction();

			return $UID;
		}
		catch (Throwable $e)
		{
			$this->logger->error($e->getMessage());
			$this->userRepositories['main']->rollBackTransaction();
			return 0;
		}
	}

	/**
	 * @throws Exception
	 */
	public function createPasswordResetToken(int $UID): string
	{
		// check first if there is another valid token
		$tokens = $this->userTokenService->findTokenByUID($UID);
		if (!empty($tokens))
		{
			$this->addErrorMessage('tokens_exists');
			return '';
		}
		try
		{
			$this->userRepositories['main']->beginTransaction();
			if($this->userRepositories['main']->update($UID, ['password' => '']) === 0)
				throw new ModuleException('users', 'Password reset failed.');

			$id = $this->userTokenService->insertToken($UID, TokenPurposes::PASSWORD_RESET);
			if($id === '')
				throw new ModuleException('users', 'Password reset failed.');

			$this->userRepositories['main']->commitTransaction();
		}
		catch (Throwable $e)
		{
			$this->logger->error($e->getMessage());
			$this->userRepositories['main']->rollBackTransaction();
			$this->addErrorMessage('password_reset_failed');
			return '';
		}
		return $id;
	}

	/**
	 * @param array<string,string> $post
	 * @throws Exception
	 */
	public function updateUser(int $UID, array $post): int
	{
		if (!$this->isUnique($UID, $post['username'], $post['email']))
		{
			return 0;
		}

		$saveData = $this->collectCommonData($post, []);

		return $this->userRepositories['main']->update($UID, $saveData);
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

		return $this->userRepositories['main']->update($UID, $data);
	}

	/**
	 * @return array<string,mixed>
	 * @throws Exception
	 */
	public function findUser(string $identifier): array
	{
		$usrMainRepository = $this->userRepositories['main'];

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
			'username' => $this->userRepositories['main']->generateWhereClause($username, '=', 'OR'),
			'email' => $this->userRepositories['main']->generateWhereClause($email, '=', 'OR')
		];
		$result =  $this->userRepositories['main']->findAllByWithFields(['UID', 'username', 'email'], $where);

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
		foreach ($this->userRepositories as $key => $repository)
		{
			if ($key === 'main')
			{
				/** @var UserMainRepository $repository */
				$userData[$key] = $repository->findByIdSecured($UID);
			}
			elseif ($key === 'acl')
			{
				/** @var UserAclRepository $repository */
				$userData[$key] = $repository->findById($UID);
			}
			else
			{
				/** @var FilterBase $repository */
				$userData[$key] = $repository->findFirstById($UID);
			}
		}

		return $userData;
	}

	private function getCacheKey(int $UID): string
	{
		return "user_$UID";
	}
}