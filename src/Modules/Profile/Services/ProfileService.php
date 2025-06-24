<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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


namespace App\Modules\Profile\Services;

use App\Framework\Services\AbstractBaseService;
use App\Modules\Users\Repositories\Edge\UserMainRepository;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

class ProfileService extends AbstractBaseService
{
	private readonly UserMainRepository $userMainRepository;

	public function __construct(UserMainRepository $userMainRepository, LoggerInterface $logger)
	{
		$this->userMainRepository = $userMainRepository;

		parent::__construct($logger);

	}

	/**
	 * @throws Exception
	 */
	public function updatePassword(string $password): int
	{
		$data = ['password' => password_hash($password, PASSWORD_DEFAULT)];

		return $this->userMainRepository->update($this->UID, $data);
	}

	/**
	 * @throws Exception
	 */
	public function updateLocale(int $UID, string $locale): int
	{
		return $this->userMainRepository->update($UID, ['locale' => $locale]);
	}


}