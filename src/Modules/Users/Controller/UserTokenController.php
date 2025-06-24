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


namespace App\Modules\Users\Controller;

use App\Framework\Controller\AbstractAsyncController;
use App\Framework\Core\CsrfToken;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\DatabaseException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Exceptions\UserException;
use App\Modules\Auth\UserSession;
use App\Modules\Profile\Services\UserTokenssService;
use App\Modules\Users\Services\AclValidator;
use DateMalformedStringException;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserTokenController extends AbstractAsyncController
{
	private readonly UserSession $userSession;
	private readonly UserTokenssService $userService;
	private readonly CsrfToken $csrfToken;
	private readonly AclValidator $aclValidator;

	public function __construct(UserSession $userSession, UserTokenssService $userService, CsrfToken $csrfToken, AclValidator $aclValidator)
	{
		$this->userSession = $userSession;
		$this->userService = $userService;
		$this->csrfToken = $csrfToken;
		$this->aclValidator = $aclValidator;
	}

	/**
	 * @throws ModuleException
	 * @throws DatabaseException
	 * @throws UserException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws DateMalformedStringException
	 * @throws Exception
	 */
	public function deleteToken(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		/** @var array<string,mixed> $post */
		$post = $request->getParsedBody();
		if (!$this->csrfToken->validateToken($post['csrf_token'] ?? ''))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'CsrF token mismatch.']);

		$token = $post['token'] ?? '';
		if ($token === '')
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Token not transmitted.']);

		$userToken = $this->userService->findByToken($token);
		if (empty($userToken))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Token not exists.']);

		if (!$this->aclValidator->isAdmin($this->userSession->getUID(), $userToken))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'No rights to delete token.']);

		if ($this->userService->deleteToken($token) == 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Token not deletable.']);

		return $this->jsonResponse($response, ['success' => true]);
	}

	public function refreshToken(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		/** @var array<string,mixed> $post */
		$post = $request->getParsedBody();
		if (!$this->csrfToken->validateToken($post['csrf_token'] ?? ''))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'CsrF token mismatch.']);

		$token = $post['token'] ?? '';
		if ($token === '')
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Token not transmitted.']);

		$userToken = $this->userService->findByToken($token);
		if (empty($userToken))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Token not exists.']);

		if (!$this->aclValidator->isAdmin($this->userSession->getUID(), $userToken))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'No rights to delete token.']);

		if ($this->userService->refreshToken($token) == 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Token not deletable.']);

		return $this->jsonResponse($response, ['success' => true]);
	}

}