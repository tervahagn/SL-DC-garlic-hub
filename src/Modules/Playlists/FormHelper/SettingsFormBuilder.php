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

namespace App\Modules\Playlists\FormHelper;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FormParameters\BaseEditParameters;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Playlists\PlaylistMode;
use App\Modules\Playlists\Services\AclValidator;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

readonly class SettingsFormBuilder
{
	private FormBuilder $formBuilder;
	private Translator $translator;
	private AclValidator $aclValidator;
	private SettingsValidator $validator;
	private SettingsParameters $parameters;
	private int $UID;
	private string $username;

	public function __construct(AclValidator $aclValidator, SettingsParameters $parameters, SettingsValidator $validator, FormBuilder $formBuilder)
	{
		$this->aclValidator = $aclValidator;
		$this->parameters   = $parameters;
		$this->validator    = $validator;
		$this->formBuilder  = $formBuilder;
	}

	public function init(Translator $translator, Session $session): static
	{
		$this->translator = $translator;
		$this->UID      = $session->get('user')['UID'];
		$this->username = $session->get('user')['username'];

		return $this;
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function buildCreateNewParameter(string $playlistMode): void
	{
		$this->parameters->addPlaylistMode();
		if (!$this->aclValidator->isSimpleAdmin($this->UID))
			return;

		$this->parameters->addUID($this->UID);

		if ($this->isTimeLimitPlaylist($playlistMode))
			$this->parameters->addTimeLimit();
	}

	/**
	 * @throws CoreException
	 * @throws Exception
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function buildEditParameter(array $playlist): void
	{
		$this->parameters->addPlaylistId();
		if (!$this->aclValidator->isAdmin($this->UID, $playlist['company_id']))
			return;

		$this->parameters->addUID();

		if ($this->isTimeLimitPlaylist($playlist['playlist_mode']))
			$this->parameters->addTimeLimit();

	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws ModuleException
	 */
	public function buildForm(array $playlist): array
	{
		$form = $this->collectFormElements($playlist);

		return $this->formBuilder->createFormular($form);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function handleUserInput(array $userInput): array
	{
		$this->parameters->setUserInputs($userInput)
			->parseInputAllParameters();

		return $this->validator->validateUserInput($userInput);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws ModuleException
	 */
	public function collectFormElements(array $playlist): array
	{
		$form       = [];
		$rules      = ['required' => true, 'minlength' => 2];

		$form['playlist_name'] = $this->formBuilder->createField([
			'type' => FieldType::TEXT,
			'id' => 'playlist_name',
			'name' => 'playlist_name',
			'title' => $this->translator->translate('playlist_name', 'playlists'),
			'label' => $this->translator->translate('playlist_name', 'playlists'),
			'value' => $playlist[SettingsParameters::PARAMETER_PLAYLIST_NAME] ?? '',
			'rules' => $rules,
			'default_value' => ''
		]);

		if ($this->parameters->hasParameter(BaseEditParameters::PARAMETER_UID))
		{
			$form['UID'] = $this->formBuilder->createField([
				'type' => FieldType::AUTOCOMPLETE,
				'id' => 'UID',
				'name' => 'UID',
				'title' => $this->translator->translate('owner', 'main'),
				'label' => $this->translator->translate('owner', 'main'),
				'value' => $playlist[BaseEditParameters::PARAMETER_UID] ?? $this->UID,
				'data-label' => $playlist['username'] ?? $this->username,
				'default_value' =>  $this->UID
			]);
		}

		if ($this->parameters->hasParameter(SettingsParameters::PARAMETER_TIME_LIMIT))
		{
			$form['time_limit'] = $this->formBuilder->createField([
				'type' => FieldType::NUMBER,
				'id' => 'time_limit',
				'name' => 'time_limit',
				'title' => $this->translator->translate('time_limit_explanation', 'playlists'),
				'label' => $this->translator->translate('time_limit', 'playlists'),
				'value' => $playlist[SettingsParameters::PARAMETER_TIME_LIMIT] ?? $this->parameters->getDefaultValueOfParameter(SettingsParameters::PARAMETER_TIME_LIMIT),
				'min'   => 0,
				'default_value' => $this->parameters->getDefaultValueOfParameter(SettingsParameters::PARAMETER_TIME_LIMIT)
			]);
		}

		if ($this->parameters->hasParameter(SettingsParameters::PARAMETER_PLAYLIST_ID))
		{
			$form['playlist_id'] = $this->formBuilder->createField([
				'type' => FieldType::HIDDEN,
				'id' => 'playlist_id',
				'name' => 'playlist_id',
				'value' => $playlist[SettingsParameters::PARAMETER_PLAYLIST_ID],
			]);
		}

		if ($this->parameters->hasParameter(SettingsParameters::PARAMETER_PLAYLIST_MODE))
		{
			$form['playlist_mode'] = $this->formBuilder->createField([
				'type' => FieldType::HIDDEN,
				'id' => 'playlist_mode',
				'name' => 'playlist_mode',
				'value' => $playlist[SettingsParameters::PARAMETER_PLAYLIST_MODE],
			]);
		}

		$form['csrf_token'] = $this->formBuilder->createField([
			'type' => FieldType::CSRF,
			'id' => BaseEditParameters::PARAMETER_CSRF_TOKEN,
			'name' => BaseEditParameters::PARAMETER_CSRF_TOKEN,
		]);

		return $form;
	}

	private function isTimeLimitPlaylist(string $playlistMode): bool
	{
		return ($playlistMode == PlaylistMode::INTERNAL->value || $playlistMode == PlaylistMode::MASTER->value);
	}

}