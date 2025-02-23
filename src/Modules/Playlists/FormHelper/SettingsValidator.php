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

use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Core\Validator;
use App\Modules\Playlists\PlaylistMode;
use Slim\Flash\Messages;

class SettingsValidator extends Validator
{
	private readonly Sanitizer $sanitizer;

	/**
	 * @param \App\Framework\Core\Sanitizer $sanitizer
	 */
	public function __construct(Sanitizer $sanitizer)
	{
		$this->sanitizer = $sanitizer;
	}


	public function validatePlaylistId(array $args): ?int
	{
		return $this->sanitizer->int($args['playlist_id'] ?? 0);
	}

	public function sanitizeUserInput($post): array
	{
		$post['playlist_name'] = $this->sanitizer->string($post['playlist_name'] ?? '');

		if (isset($post['playlist_mode']))
			$post['playlist_mode'] = $this->sanitizer->string($post['playlist_mode']);

		$post['csrf_token'] = $this->sanitizer->string($post['csrf_token'] ?? '');

		if (isset($post['time_limit']))
			$post['time_limit'] = $this->sanitizer->string($post['time_limit']);

		if (isset($post['multizone']))
			$post['multizone'] = $this->sanitizer->stringArray($post['multizone']);

		return $post;

	}

	public function validateUserInput($post, Messages $flash, Session $session)
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

		if (isset($post['playlist_mode']) && !$this->checkPlaylistMode($post['playlist_mode']))
		{
			$flash->addMessage('error', 'Playlist mode '.$post['playlist_mode'].' is not supported');
			$errors++;
		}

		if (!isset($post['csrf_token']) || $post['csrf_token'] !== $session->get('csrf_token'))
		{
			$flash->addMessage('error', 'CSRF Token mismatch');
			$errors++;
		}

		return ($errors === 0);
	}

	private function checkPlaylistMode($value): bool
	{
		return in_array($value, array_column(PlaylistMode::cases(), 'value'), true);
	}

}