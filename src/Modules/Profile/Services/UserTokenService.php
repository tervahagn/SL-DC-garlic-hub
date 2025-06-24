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

use App\Framework\Core\Crypt;
use App\Framework\Exceptions\DatabaseException;
use App\Framework\Services\AbstractBaseService;
use App\Modules\Profile\Entities\TokenPurposes;
use App\Modules\Users\Repositories\Edge\UserMainRepository;
use App\Modules\Users\Repositories\Edge\UserTokensRepository;
use DateMalformedStringException;
use DateTime;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

class UserTokenService extends AbstractBaseService
{
	private readonly UserMainRepository $userMainRepository;
	private readonly UserTokensRepository $userTokensRepository;
	private readonly Crypt $crypt;

	public function __construct(UserMainRepository $userMainRepository, UserTokensRepository $userTokensRepository, Crypt $crypt, LoggerInterface $logger)
	{
		$this->userMainRepository   = $userMainRepository;
		$this->userTokensRepository = $userTokensRepository;
		$this->crypt                = $crypt;
		parent::__construct($logger);
	}

	/**
	 * @return array{UID:int, username:string, status:int, purpose:string}|array<empty,empty>
	 * @throws DateMalformedStringException
	 * @throws Exception
	 * @throws DatabaseException
	 */
	public function findByToken(string $token): array
	{
		$result = $this->userTokensRepository->findFirstByToken($token);
		$now = new DateTime();
		if (isset($result['used_at']) && new DateTime($result['expires_at']) < $now)
			return [];

		return [
			'UID' => (int) $result['UID'],
			'username' => $result['username'],
			'company_id' => $result['company_id'],
			'status' => $result['status'],
			'purpose' => $result['purpose']
		];
	}

	/**
	 * @return list<array<string,mixed>>
	 * @throws Exception
	 */
	public function findTokenByUID(int $UID): array
	{
		return $this->userTokensRepository->findValidByUID($UID);
	}


	/**
	 * @throws Exception
	 * @throws \Exception
	 */
	public function insertToken(int $UID, TokenPurposes $purpose): string
	{
		$token = [
			'UID' => $UID,
			'purpose' => $purpose->value,
			'token' => $this->crypt->generateRandomBytes(),
			'expires_at' => date('Y-m-d H:i:s', strtotime('+12 hour'))
		];
		return (string) $this->userTokensRepository->insert($token);
	}

	/**
	 * @throws Exception
	 */
	public function updateToken(int $UID, string $purpose): int
	{
		$fields     = ['used_at' => date('Y-m-d H:i:s')];
		$conditions = ['UID' => $UID, 'purpose' => $purpose];
		return $this->userTokensRepository->updateWithWhere($fields, $conditions);
	}

	/**
	 * @throws Exception
	 */
	public function deleteToken(string $token): int
	{
		return $this->userTokensRepository->delete($token);
	}

	public function refreshToken(mixed $token)
	{
		return $this->userTokensRepository->refresh($token);
	}

}