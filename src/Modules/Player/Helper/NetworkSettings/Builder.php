<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

namespace App\Modules\Player\Helper\NetworkSettings;

use App\Framework\Core\Session;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FormParameters\BaseEditParameters;
use App\Modules\Player\Services\AclValidator;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class Builder
{
	private readonly FormElementsCreator $formElementsCreator;
	private readonly AclValidator $aclValidator;
	private readonly Validator $validator;
	private readonly Parameters $parameters;
	private int $UID;
	public function __construct(AclValidator $aclValidator, Parameters $parameters, Validator $validator, FormElementsCreator $formElementsCreator)
	{
		$this->aclValidator         = $aclValidator;
		$this->parameters           = $parameters;
		$this->validator            = $validator;
		$this->formElementsCreator  = $formElementsCreator;
	}

	public function init(Session $session): static
	{
		/** @var array{UID: int} $user */
		$user = $session->get('user');
		$this->UID      = $user['UID'];

		return $this;
	}

	/**
	 * @param array<string,mixed> $networkData
	 * @return array<string,mixed>
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function buildForm(array $networkData): array
	{
		$form       = [];
		$form[Parameters::PARAMETER_IS_INTRANET] = $this->formElementsCreator->createIsIntranet(
			$networkData[Parameters::PARAMETER_IS_INTRANET] ?? $networkData['is_intranet'] ?? 0,
		);

		$form[Parameters::PARAMETER_API_ENDPOINT] = $this->formElementsCreator->createApiEndpointField(
			$networkData[Parameters::PARAMETER_API_ENDPOINT] ?? $networkData['api_endpoint'] ?? ''
		);

		$form['player_id'] = $this->formElementsCreator->createHiddenPlayerIdField($networkData['player_id']);
		$form[BaseEditParameters::PARAMETER_CSRF_TOKEN] = $this->formElementsCreator->createCSRFTokenField();

		return $this->formElementsCreator->prepareForm($form);
	}


	/**
	 * @param array<string,mixed> $post
	 * @return array<string,mixed>
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function handleUserInput(array $post): array
	{
		$this->parameters->setUserInputs($post)
			->parseInputAllParameters();

		return $this->validator->validateUserInput();
	}

}