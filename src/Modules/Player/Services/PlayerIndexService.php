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


namespace App\Modules\Player\Services;

use App\Framework\Exceptions\ModuleException;
use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\Helper\Index\IndexFileHandler;
use App\Modules\Player\Helper\Index\PlayerDataPreparer;
use App\Modules\Player\Helper\PlayerStatus;
use Psr\Log\LoggerInterface;
use Throwable;

class PlayerIndexService
{
	private readonly PlayerDataPreparer $playerDataPreparer;
	private readonly IndexFileHandler $indexFileHandler;
	private PlayerEntity $playerEntity;
	private readonly LoggerInterface $logger;

	public function __construct(PlayerDataPreparer $playerDataPreparer, IndexFileHandler $indexHandler, LoggerInterface $logger)
	{
		$this->playerDataPreparer = $playerDataPreparer;
		$this->indexFileHandler       = $indexHandler;
		$this->logger             = $logger;
	}

	public function handleIndexRequest(string $userAgent, int $ownerId): string
	{
		// 1, parse User Agent
		// 2. Fetch Player data from DB
		// 3. select SMIL according to player status.
		// if there is player status 3 (with License)
		// 1. check for commands => generate Task scheduler
		// 2. send correct SMIL
		// 1. get the right SMIL depending on player model
		// 2. Build SMIL
		// 3. Write SMIL if it is different from previous stored
		// 4 send to player
		try
		{
			$this->playerDataPreparer->parseUserAgent($userAgent);
			$this->playerEntity = $this->playerDataPreparer->fetchDatabase();
			switch ($this->playerEntity->getStatus())
			{
				case PlayerStatus::UNREGISTERED->value:
					$this->indexFileHandler->handleNew();
					break;
				case PlayerStatus::UNRELEASED->value:
					$this->indexFileHandler->handleUnreleased();
					break;
				case PlayerStatus::RELEASED->value:
					$this->indexFileHandler->handleReleased();
					break;
				case PlayerStatus::DEBUG_FTP->value:
					$this->indexFileHandler->handleTestSMil();
					break;
				case PlayerStatus::TEST_SMIL_OK->value:
					$this->indexFileHandler->handleCorrectSMil();
					break;
				case PlayerStatus::TEST_SMIL_ERROR->value:
					$this->indexFileHandler->handleCorruptSMIL();
					break;
				case PlayerStatus::TEST_EXCEPTION->value:
					throw new ModuleException('player_index', 'Simulated exception accessing SMIL index!<br />');
				case PlayerStatus::TEST_NO_INDEX->value:
					header('Location: https://www.google.com');
					break;
				case PlayerStatus::TEST_NO_CONTENT->value:
					$this->indexFileHandler->handleCorruptContent();
					break;
				case PlayerStatus::TEST_NO_PREFETCH->value:
					$this->indexFileHandler->handleCorruptPrefetchContent();
					break;
				default:
					throw new ModuleException('player_index', 'Unknown player status: ' . $this->playerEntity->getStatus());
			}

			$filePath = $this->indexFileHandler->getFilePath();
			header('Cache-Control: public, must-revalidate, max-age=864000, pre-check=864000 ' /*proxy-revalidate*/); // 10 days
			if (isset($_SERVER['If-Modified-Since']) && (strtotime($_SERVER['If-Modified-Since']) == filemtime($filePath)))
			{
				// ok, 304 Not Modified
				header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filePath)) . ' GMT', true, 304);
			}
			else
			{
				// not cached or cache outdated, 200 OK send index.smil
				header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filePath)) . ' GMT', true, 200);
				header('Content-Length: ' . filesize($filePath));
				header("Content-Type: application/smil");
				header("Content-Description: File Transfer");
				header("Content-Disposition: attachment; filename=" . basename($filePath));
				readfile($filePath);
			}
		}
		catch (Throwable $e)
		{
			$this->logger->error('Error fetch Index: ' . $e->getMessage());
			header('Cache-Control: public, must-revalidate, max-age=864000, pre-check=864000 ' /*proxy-revalidate*/);
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT', true, 304);

			// Todo send Email with exception reason to admin maybe send an prepared error smil to call operator

		}
		return '';
	}


}