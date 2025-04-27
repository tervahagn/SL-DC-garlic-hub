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
use App\Modules\Player\Helper\PlayerModel;
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
	private string $locationData;
	private string $locationLongitude;
	private string $locationLatitude;
	private string $playlistMode;
	private array $zones;
	private array $categories;
	private array $properties;
	private array $remoteAdministration;
	private array $screenTimes;

	public function __construct(Config $config, UserAgentHandler $userAgentHandler, array $data)
	{
		$this->config = $config;
		$this->playerId             = $data['player_id'] ?? 0;
		$this->playlistId           = $data['playlist_id'] ?? 0;
		$this->UID                  = $data['UID'] ?? 0;
		$this->lastAccess           = $data['last_access'] ?? null;
		$this->lastUpdate           = $data['last_update'] ?? null;
		$this->lastUpdatePlaylist   = $data['last_update_playlist'] ?? null;
		$this->duration             = $data['duration'] ?? 0;
		$this->status               = $data['status'] ?? 1;
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
		$this->zones            = $data['multizone'] ?? [];
		$this->locationData         = $data['location_data'] ?? null;
		$this->locationLongitude    = $data['location_longitude'] ?? null;
		$this->locationLatitude     = $data['location_latitude'] ?? null;
		$this->categories           = $data['categories'] ?? [];
		$this->properties           = $data['properties'] ?? [];
		$this->remoteAdministration = $data['remote_administration'] ?? [];
		$this->screenTimes          = $data['screen_times'] ?? [];
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

	public function getModel(): int
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

	public function getZones(): array
	{
		return $this->zones;
	}

	public function getCommands(): array
	{
		return $this->commands;
	}

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

	public function getLocationData(): ?string
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

	public function getCategories(): array
	{
		return $this->categories;
	}

	public function getProperties(): array
	{
		return $this->properties;
	}

	public function getRemoteAdministration(): array
	{
		return $this->remoteAdministration;
	}

	public function getScreenTimes(): array
	{
		return $this->screenTimes;
	}

	public function getReportServer()
	{
		return $this->config->getConfigValue('report_server', 'player');
	}

	public function getIndexPath()
	{
		return $this->config->getConfigValue('index_server_url', 'player').'/'.$this->getUuid();

	}
}