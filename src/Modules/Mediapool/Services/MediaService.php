<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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


namespace App\Modules\Mediapool\Services;

use App\Modules\Mediapool\Repositories\FilesRepository;
use Ramsey\Uuid\Uuid;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

class MediaService
{
	private FilesRepository $mediaRepository;
	private LoggerInterface $logger;
	private int $UID;

	public function __construct(FilesRepository $mediaRepository, LoggerInterface $logger)
	{
		$this->mediaRepository = $mediaRepository;
		$this->logger = $logger;
	}

	public function setUID(int $UID): void
	{
		$this->UID = $UID;
	}

	/**
	 * @throws Exception
	 */
	public function listMedia(int $node_id): array
	{
		$result = $this->mediaRepository->findAllByNodeId($node_id);

		foreach ($result as &$media)
		{
			$media['metadata'] = json_decode($media['metadata'], true);
		}

		return $result;
	}

	/**
	 * @throws Exception
	 */
	public function deleteMedia(string $media_id): int
	{
		$field     = ['deleted' => 1];
		$condition = ['media_id' => $media_id];

		return $this->mediaRepository->updateWithWhere($field, $condition);
	}

	/**
	 * @throws Exception
	 */
	public function moveMedia(string $media_id, int $node_id): int
	{
		$condition = ['media_id' => $media_id];
		$field     = ['node_id'  => $node_id];

		return $this->mediaRepository->updateWithWhere($field, $condition);
	}

	/**
	 * @throws Exception
	 */
	public function copyMedia(string $media_id): int
	{
		$dataSet             = $this->mediaRepository->findById($media_id);
		$dataSet['media_id'] = Uuid::uuid4()->toString();
		$dataSet['UID']      = $this->UID;

		return $this->mediaRepository->insert($dataSet);
	}

}