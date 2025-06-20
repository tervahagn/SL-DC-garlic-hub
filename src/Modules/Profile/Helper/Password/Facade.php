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

namespace App\Modules\Profile\Helper\Password;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Profile\Services\UserService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class Facade
{
	private readonly Builder $passwordFormBuilder;
	private readonly UserService $userService;
	private readonly Parameters $passwordParameters;
	private Translator $translator;
	public function __construct(Builder $settingsFormBuilder, UserService $userService, Parameters $passwordParameters)
	{
		$this->passwordFormBuilder = $settingsFormBuilder;
		$this->userService         = $userService;
		$this->passwordParameters  = $passwordParameters;
	}

	public function init(Translator $translator, Session $session): void
	{
		$this->translator = $translator;
		/** @var array{UID: int} $user */
		$user = $session->get('user');
		$this->userService->setUID($user['UID']);
	}

	/**
	 * @param array<string,mixed> $post
	 * @return array<string,mixed>
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function configureUserFormParameter(array $post): array
	{
		return $this->passwordFormBuilder->handleUserInput($post);
	}

	/**
	 * @throws ModuleException
	 */
	public function storePassword(): int
	{
		$password = $this->passwordParameters->getValueOfParameter(Parameters::PARAMETER_PASSWORD);

		return $this->userService->updatePassword($password);
	}

	/**
	 * @return string[]
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function getUserServiceErrors(): array
	{
		$errors     = $this->userService->getErrorMessages();
		$translated =[];
		foreach ($errors as $error)
		{
			$translated[] = $this->translator->translate($error, 'profile');
		}
		return $translated;
	}


	/**
	 * @return array<string,mixed>
	 *
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function prepareUITemplate(): array
	{

		$title = $this->translator->translate('edit_password', 'profile');
		$dataSections                      = $this->passwordFormBuilder->buildForm();
		$dataSections['title']             = $title;
		$dataSections['explanations']      = $this->translator->translate('password_explanation', 'profile');
		$dataSections['additional_css']    = ['/css/profile/edit.css'];
		$dataSections['footer_modules']    = [];
		$dataSections['template_name']     = 'profile/edit';
		$dataSections['form_action']       = '/profile/password';
		$dataSections['save_button_label'] = $this->translator->translate('save', 'main');

		return $dataSections;
	}

}