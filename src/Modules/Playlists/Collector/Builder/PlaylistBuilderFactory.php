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


namespace App\Modules\Playlists\Collector\Builder;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Playlists\Collector\Contracts\ExternalContentReaderInterface;
use App\Modules\Playlists\Helper\PlaylistMode;
use App\Modules\Playlists\Collector\Contracts\ContentReaderInterface;
use App\Modules\Playlists\Collector\Contracts\PlaylistBuilderInterface;
use Psr\Log\LoggerInterface;

class PlaylistBuilderFactory
{
	private ContentReaderInterface $contentReader;
	private ExternalContentReaderInterface $externalContentReader;
	private LoggerInterface $logger;

	public function __construct(
		ContentReaderInterface $contentReader,
		ExternalContentReaderInterface $externalContentReader,
		LoggerInterface $logger
	) {
		$this->contentReader = $contentReader;
		$this->externalContentReader = $externalContentReader;
		$this->logger = $logger;
	}

	public function createBuilder(PlayerEntity $playerEntity): PlaylistBuilderInterface
	{
		if ($playerEntity->getPlaylistMode() === PlaylistMode::MULTIZONE->value) {
			return new MultiZonePlaylistBuilder(
				$playerEntity,
				$this->contentReader,
				$this->externalContentReader,
				$this->logger
			);
		}

		return new StandardPlaylistBuilder(
			$playerEntity,
			$this->contentReader,
			$this->externalContentReader,
			$this->logger
		);
	}
}
