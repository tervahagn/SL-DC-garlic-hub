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

namespace App\Modules\Playlists\Controller;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\PlaylistMode;
use App\Modules\Playlists\Services\PlaylistsService;
use App\Modules\Playlists\Services\PlaylistsOverviewService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Slim\Flash\Messages;

class ShowComposeController
{
	private readonly PlaylistsService $playlistsService;

	private Translator $translator;
	private Session $session;
	private Messages $flash;

	/**
	 * @param PlaylistsService $playlistsService
	 */
	public function __construct(PlaylistsService $playlistsService)
	{
		$this->playlistsService = $playlistsService;
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$this->setImportantAttributes($request);
		$playlistId = (int) $args['playlist_id'] ?? 0;
		if ($playlistId === 0)
			return $this->redirectWithErrors($response, 'Playlist ID not valid.');

		$playlist = $this->playlistsService->loadPlaylistForEdit($playlistId);
		if (empty($playlist))
			return $this->redirectWithErrors($response);

		switch ($playlist['playlist_mode'])
		{
			case PlaylistMode::MULTIZONE->value:
				$data = $this->buildMultizoneEditor($playlist);
			break;
			default:
				return $this->redirectWithErrors($response, 'Unsupported playlist mode: .'.$playlist['playlist_mode']);

		}

		$response->getBody()->write(serialize($data));
		return $response->withHeader('Content-Type', 'text/html');
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	private function buildMultizoneEditor(array $playlist): array
	{
		$moduleName = 'playlists';
		$exportUnits = [];
		foreach ($this->translator->translateArrayForOptions('export_unit_selects','playlists') as $key => $value)
		{
			$exportUnits[] = ['LANG_OPTION' => $value, 'VALUE_OPTION' => $key];
		}
		$title = $playlist['playlist_name']. "' ".$this->translator->translate('zone_edit',$moduleName);
		return [
			'main_layout' => [
				'LANG_PAGE_TITLE' => $title,
				'additional_css' => ['/css/playlists/zones_edit.css'],
				'footer_scripts' => ['/js/external/fabric.min.js'],
				'footer_modules' => ['/js/playlists/settings/zones_edit/init.js']
			],
			'this_layout' => [
				'template' => 'playlists/zones_edit', // Template-name
				'data' => [
					'LANG_PAGE_HEADER' => $title,
					'LANG_DUPLICATE' => $this->translator->translate('duplicate', 'templates'),
					'LANG_DELETE' => $this->translator->translate('delete', 'main'),
					'LANG_MOVE_BACKGROUND',  $this->translator->translate('move_background', 'templates'),
					'LANG_MOVE_BACK',  $this->translator->translate('move_back', 'templates'),
					'LANG_MOVE_FRONT',  $this->translator->translate('move_front', 'templates'),
					'LANG_MOVE_FOREGROUND',  $this->translator->translate('move_foreground', 'templates'),
					'PLAYLIST_ID' => $playlist['playlist_id'],
					'LANG_ADD_ZONE',  $this->translator->translate('add_zone', 'playlists'),
					'LANG_MULTIZONE_EXPORT_UNIT',  $this->translator->translate('multizone_export_unit', $moduleName),
					'export_units' => $exportUnits,
					'LANG_SCREEN_RESOLUTION' =>  $this->translator->translate('screen_resolution', $moduleName),
					'LANG_ZOOM' => $this->translator->translate('zoom', 'main'),
					'LANG_WIDTH' => $this->translator->translate('zone_width', $moduleName),
					'LANG_HEIGHT' => $this->translator->translate('zone_height', $moduleName),
					'LANG_INSERT' => $this->translator->translate('insert', 'main'),
					'LANG_SAVE'  => $this->translator->translate('save', 'main'),
					'LANG_CLOSE' => $this->translator->translate('close', 'main'),
					'LANG_CONFIRM_CLOSE_EDITOR' => $this->translator->translate('confirm_close_editor', $moduleName),
					'LANG_CANCEL' => $this->translator->translate('cancel', 'main'),
					'LANG_TRANSFER' => $this->translator->translate('transfer', 'main'),
					'LANG_PLAYLIST_NAME' =>$this->translator->translate('playlist_name', $moduleName),
					'LANG_ZONE_PROPERTIES' =>$this->translator->translate('zone_properties', $moduleName),
					'LANG_ZONES_SELECTS' =>$this->translator->translate('zones_select', $moduleName),
					'LANG_ZONE_NAME' =>$this->translator->translate('zone_name', $moduleName),
					'LANG_ZONE_LEFT' =>$this->translator->translate('zone_left', $moduleName),
					'LANG_ZONE_TOP' =>$this->translator->translate('zone_top', $moduleName),
					'LANG_ZONE_WIDTH' =>$this->translator->translate('zone_width', $moduleName),
					'LANG_ZONE_HEIGHT' =>$this->translator->translate('zone_height', $moduleName),
					'LANG_ZONE_BGCOLOR' =>$this->translator->translate('zone_bgcolor', $moduleName),
					'LANG_ZONE_TRANSPARENT' =>$this->translator->translate('zone_transparent', $moduleName)
				]
			]
		];

	}

	private function setImportantAttributes(ServerRequestInterface $request): void
	{
		$this->translator = $request->getAttribute('translator');
		$this->session    = $request->getAttribute('session');
		$this->playlistsService->setUID($this->session->get('user')['UID']);
		$this->flash      = $request->getAttribute('flash');
	}


	private function redirectWithErrors(ResponseInterface $response, string $defaultMessage = 'Unkown Error'): ResponseInterface
	{
		if ($this->playlistsService->hasErrorMessages())
		{
			foreach ($this->playlistsService->getErrorMessages() as $message)
			{
				$this->flash->addMessage('error', $message);
			}
		}
		else
		{
			$this->flash->addMessage('error', $defaultMessage);
		}
		return $response->withHeader('Location', '/playlists')->withStatus(302);
	}

	private function redirectSucceed(ResponseInterface $response, string $message): ResponseInterface
	{
		$this->flash->addMessage('success', $message);
		return $response->withHeader('Location', '/playlists')->withStatus(302);
	}


}