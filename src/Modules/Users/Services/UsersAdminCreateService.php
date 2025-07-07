<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);

namespace App\Modules\Users\Services;

use App\Framework\Exceptions\ModuleException;
use App\Framework\Services\AbstractBaseService;
use App\Modules\Mediapool\Services\NodesService;
use App\Modules\Users\Repositories\Edge\UserMainRepository;
use App\Modules\Users\UserStatus;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Throwable;

class UsersAdminCreateService extends AbstractBaseService
{
	private readonly UserMainRepository $userMainRepository;
	private readonly NodesService $nodesService;

	public function __construct(UserMainRepository $userMainRepository, NodesService $nodesService,  LoggerInterface $logger)
	{
		$this->userMainRepository = $userMainRepository;
		$this->nodesService       = $nodesService;

		parent::__construct($logger);
	}

	/**
	 * @throws Exception
	 */
	public function hasAdminUser(): bool
	{
		return ($this->userMainRepository->findByIdSecured(1) !== []);
	}

	public function creatLockfile(): bool
	{
		if (is_int(file_put_contents(INSTALL_LOCK_FILE, date('Y-m-d H:i:s'))))
			return true;

		return false;
	}

	public function logAlarm(): void
	{
		$this->logger->error('Logfile was removed. Create a new one.');
	}

	/**
	 * @param array{username:string, email:string, locale: string, password:string} $postData
	 * @throws Exception
	 */
	public function insertNewAdminUser(array $postData): int
	{
		try
		{
			$this->userMainRepository->beginTransaction();
			if (file_exists(INSTALL_LOCK_FILE))
				throw new ModuleException('users', 'There is an existing lockfile already.');

			if ($this->hasAdminUser())
				throw new ModuleException('users', 'There is an admin user already.');

			$saveData = $this->collectCommonData($postData);
			$UID = (int) $this->userMainRepository->insert($saveData);
			if ($UID !== 1)
				throw new ModuleException('users', 'Insert admin user failed.');

			$this->nodesService->UID = $UID;
			$nodeId = $this->nodesService->addUserDirectory($UID, $saveData['username']);
			if ($nodeId === 0)
				throw new ModuleException('users', 'Create mediapool admin user directory failed.');

			if (!$this->creatLockfile())
				throw new ModuleException('users', 'Lockfile could not created.');

			$this->userMainRepository->commitTransaction();
			return $UID;
		}
		catch (Throwable $e)
		{
			$this->logger->error($e->getMessage());
			$this->userMainRepository->rollBackTransaction();
			return 0;
		}
	}

	/**
	 * @param array{username:string, email:string, locale: string, password:string} $postData
	 * @return array{UID: int, username:string, email:string, locale: string, status: int, password:string}
	 */
	private function collectCommonData(array $postData): array
	{
		return [
			'UID' => 1,
			'username' => $postData['username'],
			'email' => $postData['email'],
			'locale' => $postData['locale'],
			'status' => UserStatus::ADMIN->value,
			'password' => password_hash($postData['password'], PASSWORD_DEFAULT)
		];
	}


}