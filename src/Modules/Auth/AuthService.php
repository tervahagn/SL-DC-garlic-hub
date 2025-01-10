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

use App\Framework\Core\Cookie;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\UserException;
use App\Framework\User\UserEntity;
use App\Framework\User\UserService;
use DateTime;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;

class AuthService
{
	const string COOKIE_NAME_AUTO_LOGIN = 'UserLogin';
	const string AUTOLOGIN_EXPIRE = '+28 days';
	private UserService $userService;
	private Cookie $cookie;
	private string $errorMessage;
	private LoggerInterface $logger;

	public function __construct(UserService $userService, Cookie $cookie, LoggerInterface $logger)
	{
		$this->userService = $userService;
		$this->cookie      = $cookie;
		$this->logger      = $logger;
	}

	public function getErrorMessage(): string
	{
		return $this->errorMessage;
	}

	/**
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws UserException
	 */
	public function login(string $identifier, string $password): ?UserEntity
	{
		$userData = $this->userService->findUser($identifier);

		if (empty($userData) || !password_verify($password, $userData['password']))
		{
			$this->errorMessage = 'Invalid credentials.';
			$this->logger->error('Login failed', [
				'username' => $identifier,
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
				'time' => date('Y-m-d H:i:s'),
				'reason' => 'Invalid credentials.',
			]);
			return null;
		}
		$this->validateUserStatus($userData['status']);
		if (!empty($this->errorMessage))
			return null;

		$this->userService->invalidateCache($userData['UID']);

		return $this->getCurrentUser($userData['UID']);
	}

	/**
	 * @throws Exception
	 * @throws FrameworkException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws UserException
	 */
	public function loginByCookie(): ?UserEntity
	{
		// no cookie? that's it
		if (!$this->cookie->hasCookie(self::COOKIE_NAME_AUTO_LOGIN))
		{
			$this->errorMessage = 'No cookie for autologin was found.';
			return null;
		}

		$cookie_payload = $this->cookie->getHashedCookie(self::COOKIE_NAME_AUTO_LOGIN);
		$UID = (int) $cookie_payload['UID'];
		if ($UID < 1)
		{
			$this->errorMessage = 'No valid UID found';
			return null;
		}

		return $this->loginSilent($UID, $cookie_payload['sid']);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 * @throws UserException
	 */
	public function loginSilent(int $UID, string $sessionId): ?UserEntity
	{
		$userEntity = $this->getCurrentUser($UID);
		$this->validateUserStatus($userEntity->getMain()['status']);
		if (!empty($this->errorMessage))
			return null;

		$this->userService->updateUserStats($UID, $sessionId);
		return $userEntity;
	}

	/**
	 * @throws FrameworkException
	 */
	public function createAutologinCookie(int $UID, string $sessionId): void
	{
		$payload = ['UID' => $UID, 'sid' => $sessionId];
		$this->cookie->createHashedCookie(self::COOKIE_NAME_AUTO_LOGIN, $payload, new DateTime(self::AUTOLOGIN_EXPIRE));
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function logout(array $user): void
	{
		$this->userService->invalidateCache($user['UID']);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function getCurrentUser(int $UID): UserEntity
	{
		return $this->userService->getCurrentUser($UID);
	}

	private function validateUserStatus(int $status): void
	{
		switch ($status)
		{
			case UserService::USER_STATUS_DELETED:
				$this->errorMessage = 'login//account_deleted';
				break;

			case UserService::USER_STATUS_LOCKED:
				$this->errorMessage = 'login//account_locked';
				break;

			case UserService::USER_STATUS_REGISTERED:
				$this->errorMessage = 'login//account_inactive';
				break;
		}
	}

}