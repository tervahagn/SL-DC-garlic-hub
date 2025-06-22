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

namespace App\Modules\Users\Helper\Settings;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Users\Services\UsersService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class Facade
{
	private readonly Builder $settingsFormBuilder;
	private readonly UsersService $usersService;
	private readonly Parameters $settingsParameters;
	/** @var array<string, mixed> */
	private array $oldUser;
	private Translator $translator;

	public function __construct(Builder $settingsFormBuilder, UsersService $usersService, Parameters $settingsParameters)
	{
		$this->settingsFormBuilder = $settingsFormBuilder;
		$this->usersService    = $usersService;
		$this->settingsParameters  = $settingsParameters;
	}

	public function init(Translator $translator, Session $session): void
	{
		$this->translator = $translator;
		$this->settingsFormBuilder->init($session);
		/** @var array{UID: int, username: string} $user */
		$user = $session->get('user');
		$this->usersService->setUID($user['UID']);
	}

	/**
	 * @return array<string,mixed>
	 * @throws Exception
	 */
	public function loadUserForEdit(int $UID): array
	{
		$this->oldUser = $this->usersService->loadForEdit($UID);

		return $this->oldUser;
	}

	/**
	 * @param array<string,mixed> $post
	 * @return array<string,mixed>
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 * @throws Exception
	 */
	public function configureUserFormParameter(array $post): array
	{
		$UID = $post['UID'] ?? 0;
		if ($UID > 0)
		{
			$this->loadUserForEdit($UID);
			$this->settingsFormBuilder->configEditParameter($this->oldUser);
		}
		else
		{
			$this->settingsFormBuilder->configNewParameter();
		}

		return $this->settingsFormBuilder->handleUserInput($post);
	}

	/**
	 * @throws Exception
	 */
	public function storeUser(int $UID): int
	{
		$saveData  = array_combine(
			$this->settingsParameters->getInputParametersKeys(),
			$this->settingsParameters->getInputValuesArray()
		);

		if ($UID > 0)
			$id = $this->usersService->updateUser($UID, $saveData);
		else
			$id = $this->usersService->insertNewUser($saveData);

		return $id;
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
		$errors     = $this->usersService->getErrorMessages();
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
	public function buildCreateNewParameter(): void
	{
		$this->settingsFormBuilder->configNewParameter();
	}

	/**
	 * @param array<string,int|string> $user
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 * @throws ModuleException
	 */
	public function buildEditParameter(array $user): void
	{
		$this->settingsFormBuilder->configEditParameter($user);
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

		$title = $this->translator->translate('core_data', 'users'). ': ' .$name;
		$dataSections                      = $this->settingsFormBuilder->buildForm($post);
		$dataSections['title']             = $title;
		$dataSections['additional_css']    = ['/css/users/edit.css'];
		$dataSections['footer_modules']    = [];
		$dataSections['template_name']     = 'users/edit';
		$dataSections['form_action']       = '/users/edit';
		$dataSections['save_button_label'] = $this->translator->translate('save', 'main');
		$dataSections['additional_buttons'] = $this->settingsFormBuilder->addButtons();

		return $dataSections;
	}

}