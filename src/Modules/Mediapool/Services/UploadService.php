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
use Ramsey\Uuid\Uuid;

class UploadService
{
	private MediaHandlerFactory $mediaHandlerFactory;
	private FilesRepository $mediaRepository;
	private MimeTypeDetector $mimeTypeDetector;

	/**
	 * @param MediaHandlerFactory $mediaHandlerFactory
	 * @param FilesRepository     $mediaRepository
	 */
	public function __construct(MediaHandlerFactory $mediaHandlerFactory, FilesRepository $mediaRepository, MimeTypeDetector $mimeTypeDetector)
	{
		$this->mediaHandlerFactory = $mediaHandlerFactory;
		$this->mediaRepository     = $mediaRepository;
		$this->mimeTypeDetector    = $mimeTypeDetector;
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
				$mediaHandler->checkFileAfterUpload($uploadedFile);
				$newFilename  = $mediaHandler->determineNewFilename($uploadPath);

				$fileInfo    = pathinfo($uploadedFile->getClientFilename());
				$filePath    = $fileInfo['dirname']. '/'.$newFilename.'.'.$fileInfo['extension'];
				$mediaHandler->createThumbnail($filePath);

				$fileData = [
					'media_id'  => Uuid::uuid4()->toString(),
					'node_id'   => $node_id,
					'UID'       => $UID,
					'checksum'  => $newFilename,
					'mimetype'  => $this->mimeTypeDetector->detectFromFile($filePath),
					'metadata'  => json_encode([
						'size'       => $mediaHandler->getFileSize(),
						'dimensions' => $mediaHandler->getDimensions()])
				];

				$this->mediaRepository->insert($fileData);
			}
			catch(\Exception | FilesystemException $e)
			{

			}
		}
	}




}