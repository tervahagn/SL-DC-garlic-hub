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

use App\Framework\Core\Translate\Translator;
use App\Framework\Core\Validator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\PlaylistMode;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class SettingsValidator extends Validator
{
	private Translator $translator;
	private SettingsParameters $inputEditParameters;

	/**
	 * @param Translator $translator
	 * @param SettingsParameters $inputEditParameters
	 */
	public function __construct(Translator $translator, SettingsParameters $inputEditParameters)
	{
		$this->translator = $translator;
		$this->inputEditParameters = $inputEditParameters;
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function validateUserInput(array $userInputs): array
	{
		$this->inputEditParameters->checkCsrfToken();

		$errors = [];
		if (empty($this->inputEditParameters->getValueOfParameter(SettingsParameters::PARAMETER_PLAYLIST_NAME)))
			$errors[] = $this->translator->translate('no_playlist_name', 'playlists');

		// we need userInput here as getValueOfParameter will throw an exception if not set
		if (!isset($userInputs[SettingsParameters::PARAMETER_PLAYLIST_MODE]) && !isset($userInputs[SettingsParameters::PARAMETER_PLAYLIST_ID]))
			$errors[] = $this->translator->translate('parameters_missing', 'playlists');

		if (isset($userInputs[SettingsParameters::PARAMETER_PLAYLIST_MODE]) && !$this->checkPlaylistMode($userInputs))
			$errors[] = sprintf($this->translator->translate('playlist_mode_unsupported', 'playlists'), $userInputs['playlist_mode']);

		return $errors;
	}

	private function checkPlaylistMode(array $userInputs): bool
	{
		return in_array($userInputs[SettingsParameters::PARAMETER_PLAYLIST_MODE], array_column(PlaylistMode::cases(), 'value'), true);
	}

}