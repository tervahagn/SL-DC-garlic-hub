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

use App\Modules\Playlists\Services\PlaylistsService;

class Facade
{
	private readonly FormCreator $settingsFormBuilder;
	private readonly PlaylistsService $playlistsService;
	private readonly Parameters $settingsParameters;

	public function __construct(FormCreator $settingsFormBuilder, PlaylistsService $playlistsService, Parameters $settingsParameters)
	{
		$this->settingsFormBuilder = $settingsFormBuilder;
		$this->playlistsService = $playlistsService;
		$this->settingsParameters = $settingsParameters;
	}


}