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


namespace App\Modules\Player\Entities;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Modules\Player\Enums\PlayerModel;
use App\Modules\Player\IndexCreation\UserAgentHandler;
use DateTime;

class PlayerEntity
{
	private readonly Config $config;
	private int $playerId;
	private int $playlistId;
	private int $UID;
	private DateTime $lastAccess;
	private DateTime $lastUpdate;
	private DateTime $lastUpdatePlaylist;
	private int $status;
	private int $refresh;
	private int $licenceId;
	private PlayerModel $model;
	private string $uuid;
	private array $commands;
	private array $reports;
	private string $firmwareVersion;
	private string $playerName;
	private string $playlistName;
	private int $duration;
	private array $locationData;
	private string $locationLongitude;
	private string $locationLatitude;
	private string $playlistMode;
	private array $zones;
	private array $categories;
	private array $properties;
	private array $remoteAdministration;
	private array $screenTimes;

	/**
	 * @param array<string,mixed> $data
	 */
	public function __construct(Config $config, UserAgentHandler $userAgentHandler, array $data)
	{
		$format = 'Y-m-d H:i:s';
		$default = '2001-01-01 00:00:00';

		$this->config = $config;
		$this->playerId             = $data['player_id'] ?? 1;
		$this->playlistId           = $data['playlist_id'] ?? 0;
		$this->UID                  = $data['UID'] ?? 1;

		$this->lastAccess           = DateTime::createFromFormat($format,$data['last_access'] ?? $default);
		$this->lastUpdate           = DateTime::createFromFormat($format,$data['last_update'] ?? $default);
		$this->lastUpdatePlaylist   = DateTime::createFromFormat($format,$data['last_update_playlist'] ?? $default);
		$this->duration             = $data['duration'] ?? 0;
		$this->status               = $data['status'] ?? 0;
		$this->refresh              = $data['refresh'] ?? 900;
		$this->licenceId            = $data['licence_id'] ?? 0;
		$this->model                = $userAgentHandler->getModel();
		$this->uuid                 = $userAgentHandler->getUuid();
		$this->commands             = $data['commands'] ?? [];
		$this->reports              = $data['reports'] ?? [];
		$this->firmwareVersion      = $userAgentHandler->getFirmware();
		$this->playerName           = $userAgentHandler->getName();
		$this->playlistName         = $data['playlist_name'] ?? '';
		$this->playlistMode         = $data['playlist_mode'] ?? '';
		$this->zones                = $data['multizone'] ?? [];
		$this->locationData         = $data['location_data'] ?? [];
		$this->locationLongitude    = $data['location_longitude'] ?? '';
		$this->locationLatitude     = $data['location_latitude'] ?? '';
		$this->categories           = $data['categories'] ?? [];
		$this->properties           = $data['properties'] ?? [];
		$this->remoteAdministration = $data['remote_administration'] ?? [];
		$this->screenTimes          = $data['screen_times'] ?? [];

		if (empty($this->properties))
			$this->properties = ['width' => 1920, 'height' => 1080];

	}

	public function getPlayerId(): int
	{
		return $this->playerId;
	}

	public function getPlaylistId(): int
	{
		return $this->playlistId;
	}

	public function getUID(): int
	{
		return $this->UID;
	}

	public function getLastAccess(): DateTime
	{
		return $this->lastAccess;
	}

	public function getLastUpdate(): DateTime
	{
		return $this->lastUpdate;
	}

	public function getStatus(): int
	{
		return $this->status;
	}

	public function getRefresh(): int
	{
		return $this->refresh;
	}

	public function getDuration(): int
	{
		return $this->duration;
	}

	public function getLicenceId(): int
	{
		return $this->licenceId;
	}

	public function getModel(): PlayerModel
	{
		return $this->model;
	}

	public function getUuid(): string
	{
		return $this->uuid;
	}

	public function getLastUpdatePlaylist(): DateTime
	{
		return $this->lastUpdatePlaylist;
	}

	public function getPlaylistName(): string
	{
		return $this->playlistName;
	}

	public function getPlaylistMode(): string
	{
		return $this->playlistMode;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getZones(): array
	{
		return $this->zones;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getCommands(): array
	{
		return $this->commands;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getReports(): array
	{
		return $this->reports;
	}

	public function getFirmwareVersion(): string
	{
		return $this->firmwareVersion;
	}

	public function getPlayerName(): string
	{
		return $this->playerName;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getLocationData(): array
	{
		return $this->locationData;
	}

	public function getLocationLongitude(): string
	{
		return $this->locationLongitude;
	}

	public function getLocationLatitude(): string
	{
		return $this->locationLatitude;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getCategories(): array
	{
		return $this->categories;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getProperties(): array
	{
		return $this->properties;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getRemoteAdministration(): array
	{
		return $this->remoteAdministration;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getScreenTimes(): array
	{
		return $this->screenTimes;
	}

	/**
	 * @throws CoreException
	 */
	public function getReportServer(): string
	{
		return $this->config->getConfigValue('report_server', 'player');
	}

	/**
	 * @throws CoreException
	 */
	public function getIndexPath(): string
	{
		return $this->config->getConfigValue('index_server_url', 'player').'/'.$this->getUuid();

	}
}