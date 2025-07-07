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

use App\Framework\Core\Config\Config;
use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Users\Helper\Settings\Parameters;
use App\Modules\Users\Services\UsersAdminService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class Facade
{
	private readonly Builder $settingsFormBuilder;
	private readonly UsersAdminService $usersAdminService;
	private readonly Config $config;
	private readonly Parameters $settingsParameters;
	private Translator $translator;

	 public function __construct(Builder $settingsFormBuilder, UsersAdminService $usersAdminService, Config $config, Parameters $settingsParameters)
	{
		$this->settingsFormBuilder = $settingsFormBuilder;
		$this->usersAdminService   = $usersAdminService;
		$this->config              = $config;
		$this->settingsParameters  = $settingsParameters;
	}

	public function init(Translator $translator, Session $session): void
	{
		$this->translator = $translator;
		$this->settingsFormBuilder->init($session);
		/** @var array{UID: int, username: string} $user */
		$user = $session->get('user');
		$this->usersAdminService->setUID($user['UID']);
	}

	/**
	 * @param array<string,mixed> $post
	 * @return string[]
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 * @throws Exception
	 */
	public function configureUserFormParameter(array $post): array
	{
		$this->settingsFormBuilder->configParameter();

		return $this->settingsFormBuilder->handleUserInput($post);
	}


	/**
	 * @throws Exception
	 */
	public function storeUser(): int
	{
		/** @var array{username:string, email:string, locale?: string, status?: int} $saveData */
		$saveData  = array_combine(
			$this->settingsParameters->getInputParametersKeys(),
			$this->settingsParameters->getInputValuesArray()
		);

		return $this->usersAdminService->insertNewUser($saveData);
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
		$errors     = $this->usersAdminService->getErrorMessages();
		$translated =[];
		foreach ($errors as $error)
		{
			$translated[] = $this->translator->translate($error, 'users');
		}
		return $translated;
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws Exception
	 */
	public function buildCreateParameter(): void
	{
		$this->settingsFormBuilder->configParameter();
	}

	/**
	 * @param array<string,string> $post
	 * @return array<string,mixed>
	 *
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function prepareUITemplate(array $post): array
	{
		$name = $this->oldUser['username'] ?? $this->translator->translate('add', 'users');

		$passwordPattern = $this->config->getConfigValue('password_pattern', 'main');
		$title = $this->translator->translate('create_admin_user', 'users'). ': ' .$name;
		$dataSections                      = $this->settingsFormBuilder->buildForm($passwordPattern);
		$dataSections['title']             = $title;
		$dataSections['additional_css']    = ['/css/users/edit.css'];
		$dataSections['footer_modules']    = ['/js/users/edit/init.js'];
		$dataSections['template_name']     = 'users/edit';
		$dataSections['form_action']       = '/users/edit';
		$dataSections['save_button_label'] = $this->translator->translate('save', 'main');

		return $dataSections;
	}

}