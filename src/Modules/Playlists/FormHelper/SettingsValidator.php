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

use Slim\Flash\Messages;

class SettingsValidator
{

	public function validatePlaylistId(array $args): ?int
	{
		return isset($args['playlist_id']) && (int)$args['playlist_id'] > 0 ? (int)$args['playlist_id'] : null;
	}

	public function validateCreate($post, Messages $flash)
	{
		$errors = 0;
		if (!isset($post['playlist_name']) || empty(trim($post['playlist_name'])))
		{
			$flash->addMessage('error', 'Missing playlist name');
			$errors++;
		}

		if (!isset($post['playlist_mode']) && !isset($post['playlist_id']))
		{
			$flash->addMessage('error', 'Relevant parameters are missing');
			$errors++;
		}

		return ($errors === 0);
	}

}