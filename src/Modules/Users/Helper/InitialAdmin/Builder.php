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

namespace App\Modules\Users\Helper\InitialAdmin;

use App\Framework\Core\Session;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Users\Services\AclValidator;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class Builder
{
	private readonly FormElementsCreator $formElementsCreator;
	private readonly Validator $validator;
	private readonly Parameters $parameters;
	private int $UID;
	public function __construct( Parameters $parameters, Validator $validator, FormElementsCreator $formElementsCreator)
	{
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
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function configParameter(): void
	{
	}

	/**
	 * @return array<string,mixed>
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function buildForm(string $pattern): array
	{
		$form       = [];
		$form['username'] = $this->formElementsCreator->createUserNameField(
			$user[Parameters::PARAMETER_ADMIN_NAME] ?? ''
		);

		$form['email'] = $this->formElementsCreator->createEmailField(
			$user[Parameters::PARAMETER_ADMIN_EMAIL] ?? ''
		);
		$form['password']         = $this->formElementsCreator->createPasswordField('', $pattern);
		$form['password_confirm'] = $this->formElementsCreator->createPasswordConfirmField('');

		$form['locale'] = $this->formElementsCreator->createUserLocaleField($user[Parameters::PARAMETER_ADMIN_LOCALE] ?? 'en_US');

		$form['csrf_token'] = $this->formElementsCreator->createCSRFTokenField();

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