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

namespace App\Modules\Playlists\Helper\Settings;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FormParameters\BaseEditParameters;
use App\Framework\Utils\FormParameters\BaseParameters;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Playlists\Helper\PlaylistMode;
use App\Modules\Playlists\Services\AclValidator;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

readonly class Builder
{
	private readonly Collector $collector;
	private readonly AclValidator $aclValidator;
	private readonly Validator $validator;
	private readonly Parameters $parameters;
	private readonly int $UID;
	private readonly string $username;

	public function __construct(AclValidator $aclValidator, Parameters $parameters, Validator $validator, Collector $collector)
	{
		$this->aclValidator = $aclValidator;
		$this->parameters   = $parameters;
		$this->validator    = $validator;
		$this->collector    = $collector;
	}

	public function init(Session $session): static
	{
		$user = $session->get('user');
		$this->UID      = $user['UID'];
		$this->username = $user['username'];

		return $this;
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function configNewParameter(string $playlistMode): void
	{
		$this->parameters->addPlaylistMode();
		if (!$this->aclValidator->isSimpleAdmin($this->UID))
			return;

		$this->parameters->addOwner();

		if ($this->isTimeLimitPlaylist($playlistMode))
			$this->parameters->addTimeLimit();
	}

	/**
	 * @throws CoreException
	 * @throws Exception
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function configEditParameter(array $playlist): void
	{
		$this->parameters->addPlaylistId();
		if (!$this->aclValidator->isAdmin($this->UID, $playlist['company_id']))
			return;

		$this->parameters->addOwner();

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
		$form       = [];
		$form['playlist_name'] = $this->collector->createPlaylistNameField(
			$playlist[Parameters::PARAMETER_PLAYLIST_NAME] ?? ''
		);

		if ($this->parameters->hasParameter(BaseEditParameters::PARAMETER_UID))
		{
			$form['UID'] = $this->collector->createUIDField(
				$playlist[BaseParameters::PARAMETER_UID] ?? $this->UID,
				$playlist['username'] ?? $this->username,
				$this->UID
			);
		}

		if ($this->parameters->hasParameter(Parameters::PARAMETER_TIME_LIMIT))
		{
			$form['time_limit'] = $this->collector->createTimeLimitField(
				$playlist[Parameters::PARAMETER_TIME_LIMIT] ?? $this->parameters->getDefaultValueOfParameter(Parameters::PARAMETER_TIME_LIMIT),
				$this->parameters->getDefaultValueOfParameter(Parameters::PARAMETER_TIME_LIMIT)
			);
		}

		if ($this->parameters->hasParameter(Parameters::PARAMETER_PLAYLIST_ID))
			$form['playlist_id'] = $this->collector->createHiddenPlaylistIdField($playlist[Parameters::PARAMETER_PLAYLIST_ID]);

		if ($this->parameters->hasParameter(Parameters::PARAMETER_PLAYLIST_MODE))
			$form['playlist_mode'] = $this->collector->createPlaylistModeField($playlist[Parameters::PARAMETER_PLAYLIST_MODE]);

		$form['csrf_token'] = $this->collector->createCSRFTokenField();

		return $this->collector->prepareForm($form);
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


	private function isTimeLimitPlaylist(string $playlistMode): bool
	{
		return ($playlistMode == PlaylistMode::INTERNAL->value || $playlistMode == PlaylistMode::MASTER->value);
	}

}