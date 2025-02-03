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

use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\Repositories\FilesRepository;
use App\Modules\Mediapool\Utils\AbstractMediaHandler;
use App\Modules\Mediapool\Utils\MediaHandlerFactory;
use App\Modules\Mediapool\Utils\MimeTypeDetector;
use Doctrine\DBAL\Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Slim\Psr7\UploadedFile;

class UploadService
{
	private MediaHandlerFactory $mediaHandlerFactory;
	private FilesRepository $mediaRepository;
	private MimeTypeDetector $mimeTypeDetector;
	private Client $client;
	private LoggerInterface $logger;

	/**
	 * @param MediaHandlerFactory $mediaHandlerFactory
	 * @param Client $client
	 * @param FilesRepository $mediaRepository
	 * @param MimeTypeDetector $mimeTypeDetector
	 * @param LoggerInterface $logger
	 */
	public function __construct(MediaHandlerFactory $mediaHandlerFactory, Client $client, FilesRepository
$mediaRepository, MimeTypeDetector $mimeTypeDetector, LoggerInterface $logger)
	{
		$this->mediaHandlerFactory = $mediaHandlerFactory;
		$this->client              = $client;
		$this->mediaRepository     = $mediaRepository;
		$this->mimeTypeDetector    = $mimeTypeDetector;
		$this->logger              = $logger;
	}

	/**
	 * @throws GuzzleException
	 */
	public function requestApi(string $apiUrl, $headers = []): array
	{
		$options = [];
		if (!empty($headers))
			$options['headers'] =  $headers;

		$response = $this->client->request('GET', $apiUrl, $options);

		return json_decode($response->getBody(), true);
	}

	/**
	 * Upload Media and insert them into database. The filename is a sh256 hash of the file
	 *
	 * if this method finds a media already uploaded it will create only an entry into the database
	 * and delete the new uploaded file.
	 *
	 * media_id is a UUID to make it more difficult to guess
	 *
	 * @throws Exception
	 */
	public function uploadMediaFiles(int $nodeId, int $UID, UploadedFile $uploadedFile, array $metadata): array
	{
		$ret = [];
		try
		{
			$preMimeType      = $uploadedFile->getClientMediaType();
			if ($uploadedFile->getError() !== UPLOAD_ERR_OK)
				throw new ModuleException('mediapool', $this->codeToMessage($uploadedFile->getError()));

			$mediaHandler = $this->mediaHandlerFactory->createHandler($preMimeType);
			$mediaHandler->setMetadata($metadata);
			$mediaHandler->checkFileBeforeUpload($uploadedFile->getSize());
			$uploadPath   = $mediaHandler->uploadFromLocal($uploadedFile);

			$ret[] = $this->insertDataset($mediaHandler, $uploadPath, $nodeId, $UID);
		}
		catch(\Exception | FilesystemException $e)
		{
			$this->logger->error('UploadService Error: '.$e->getMessage());
			$ret[] = ['success' => false, 'error_message' => $e->getMessage()];
		}
		return $ret;
	}

	public function uploadExternalMedia(int $nodeId, int $UID, string $externalLink, array $extMetadata): array
	{
		try
		{
			$response      = $this->client->head($externalLink);
			$preMimeType   = $response->getHeaderLine('Content-Type');
			$contentLength = $response->getHeaderLine('Content-Length');
			$mediaHandler  = $this->mediaHandlerFactory->createHandler($preMimeType);
			$mediaHandler->checkFileBeforeUpload($contentLength);
			$uploadPath    = $mediaHandler->uploadFromExternal($this->client, $externalLink);

			$ret = $this->insertDataset($mediaHandler, $uploadPath, $nodeId, $UID, $extMetadata);
		}
		catch (ClientException|GuzzleException|CoreException|ModuleException|Exception|FilesystemException $e)
		{
			$this->logger->error('UploadService Error: '.$e->getMessage());
			$ret = ['success' => false, 'error_message' => $e->getMessage()];
		}

		return $ret;
	}


	/**
	 * @throws ModuleException
	 * @throws FilesystemException
	 * @throws Exception
	 */
	private function insertDataset(AbstractMediaHandler $mediaHandler, string $uploadedPath, int $nodeId, int $UID,
								   array $extMetadata):
	array
	{
		$fileHash = $mediaHandler->determineNewFilename($uploadedPath);
		$dataSet  = $this->mediaRepository->findFirstBy(['checksum' => $fileHash]);
		$pathInfo = pathinfo($uploadedPath);

		if (empty($dataSet))
		{
			$mimetype    = $this->mimeTypeDetector->detectFromFile($mediaHandler->getAbsolutePath($uploadedPath));
			$newFilePath = $mediaHandler->determineNewFilePath($uploadedPath, $fileHash, $mimetype);
			$mediaHandler->rename($uploadedPath, $newFilePath);
			$mediaHandler->checkFileAfterUpload($newFilePath);
			$mediaHandler->createThumbnail($newFilePath);

			$metadata         = [
					'size'       => $mediaHandler->getFileSize(),
					'dimensions' => $mediaHandler->getDimensions(),
					'duration'   => $mediaHandler->getDuration()
			];
			$description = '';
			foreach ($extMetadata as $key => $value)
			{
				$metadata[$key] = $value;

				if ($key === 'description')
					$description = $value;
			}
			$metadata  = json_encode($metadata);
			$extension = pathinfo($newFilePath, PATHINFO_EXTENSION);
			$thumbExtension = $mediaHandler->getThumbExtension();
		}
		else
		{
			$mediaHandler->removeUploadedFile($uploadedPath);
			$mimetype    = $dataSet['mimetype'];
			$metadata       = $dataSet['metadata'];
			$extension      = $dataSet['extension'];
			$description    = $dataSet['media_description'];
			$thumbExtension = $dataSet['thumb_extension'];
		}

		$fileData = [
			'media_id'  => Uuid::uuid4()->toString(),
			'node_id'   => $nodeId,
			'UID'       => $UID,
			'checksum'  => $fileHash,
			'mimetype'  => $mimetype,
			'metadata'  => $metadata,
			'filename'  => $pathInfo['basename'],
			'extension' => $extension,
			'thumb_extension' => $thumbExtension,
			'media_description' => $description
		];
		$this->mediaRepository->insert($fileData);

		return ['success' => true, 'message' => $pathInfo['basename'].' successful uploaded'];
	}

	private function codeToMessage(int $code): string
	{
		return match ($code)
		{
			UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
			UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
			UPLOAD_ERR_PARTIAL    => "The uploaded file was only partially uploaded",
			UPLOAD_ERR_NO_FILE    => "No file was uploaded",
			UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
			UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
			UPLOAD_ERR_EXTENSION  => "File upload stopped by extension",
			default => "Unknown upload error",
		};
	}

}