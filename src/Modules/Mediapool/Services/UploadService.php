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
use App\Modules\Mediapool\Utils\MimeTypeDetector;
use Doctrine\DBAL\Exception;
use League\Flysystem\FilesystemException;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class UploadService
{
	private MediaHandlerFactory $mediaHandlerFactory;
	private FilesRepository $mediaRepository;
	private MimeTypeDetector $mimeTypeDetector;
	private LoggerInterface $logger;

	/**
	 * @param MediaHandlerFactory $mediaHandlerFactory
	 * @param FilesRepository     $mediaRepository
	 */
	public function __construct(MediaHandlerFactory $mediaHandlerFactory, FilesRepository $mediaRepository, MimeTypeDetector $mimeTypeDetector, LoggerInterface $logger)
	{
		$this->mediaHandlerFactory = $mediaHandlerFactory;
		$this->mediaRepository     = $mediaRepository;
		$this->mimeTypeDetector    = $mimeTypeDetector;
		$this->logger              = $logger;
	}

	/**
	 * @throws Exception
	 */
	public function uploadMedia(int $node_id, int $UID, array $uploadedFiles): void
	{
		foreach ($uploadedFiles as $uploadedFile)
		{
			try
			{
				$mediaHandler = $this->mediaHandlerFactory->createHandler($uploadedFile->getClientMediaType());
				$mediaHandler->checkFileBeforeUpload($uploadedFile);
				$uploadPath   = $mediaHandler->upload($uploadedFile);
				$fileHash     = $mediaHandler->determineNewFilename($uploadPath);
				$originalPath = $mediaHandler->rename($uploadPath, $fileHash);

				$mediaHandler->checkFileAfterUpload($originalPath);
				$mediaHandler->createThumbnail($originalPath);

				$absoluteFilePath = $mediaHandler->getAbsolutePath($originalPath);

				$fileData = [
					'media_id'  => Uuid::uuid4()->toString(),
					'node_id'   => $node_id,
					'UID'       => $UID,
					'checksum'  => $fileHash,
					'mimetype'  => $this->mimeTypeDetector->detectFromFile($absoluteFilePath),
					'metadata'  => json_encode([
						'size'       => $mediaHandler->getFileSize(),
						'dimensions' => $mediaHandler->getDimensions()])
				];

				$this->mediaRepository->insert($fileData);
			}
			catch(\Exception | FilesystemException $e)
			{
				$this->logger->error('UploadService Error: '.$e->getMessage());
			}
		}
	}




}