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
use App\Modules\Mediapool\Repositories\QueueRepository;
use App\Modules\Mediapool\Utils\MediaHandlerFactory;
use Doctrine\DBAL\Exception;
use Ramsey\Uuid\Uuid;

class UploadService
{
	private MediaHandlerFactory $mediaHandlerFactory;
	private FilesRepository $mediaRepository;
	private QueueRepository $queueRepository;

	/**
	 * @param MediaHandlerFactory $mediaHandlerFactory
	 * @param FilesRepository     $mediaRepository
	 * @param QueueRepository     $queueRepository
	 */
	public function __construct(MediaHandlerFactory $mediaHandlerFactory, FilesRepository $mediaRepository,	QueueRepository $queueRepository)
	{
		$this->mediaHandlerFactory = $mediaHandlerFactory;
		$this->mediaRepository     = $mediaRepository;
		$this->queueRepository     = $queueRepository;
	}

	/**
	 * @throws Exception
	 */
	public function uploadMediaToQueue(int $node_id, int $UID, array $uploadedFiles): void
	{
		foreach ($uploadedFiles as $uploadedFile)
		{
			/** @var \Psr\Http\Message\UploadedFileInterface $uploadedFile */
			$fileInfo = pathinfo($uploadedFile->getClientFilename());

			$fileData = [
				'queue_id'  => Uuid::uuid4()->toString(),
				'node_id'   => $node_id,
				'status'    => 0,
				'UID'       => $UID,
				'filename'  => $fileInfo['basename'],
				'extension' => $fileInfo['extension'],
				'mimetype'  => $uploadedFile->getClientMediaType(),
				'metadata'  => json_encode(['size' => $uploadedFile->getSize()])
			];

			$this->queueRepository->insert($fileData);
		}
	}

	public function processQueue(): void
	{
		$queue = $this->queueRepository->findAllBy();

		foreach ($queue as $file)
		{
			$mediaHandler = $this->mediaHandlerFactory->createHandler($file['mimetype']);
			$mediaHandler->createThumbnail($file);
		}
	}



}