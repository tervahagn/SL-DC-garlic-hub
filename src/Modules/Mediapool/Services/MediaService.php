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
use App\Framework\Exceptions\DatabaseException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\Repositories\FilesRepository;
use App\Modules\Mediapool\Repositories\NodesRepository;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Ramsey\Uuid\Uuid;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

class MediaService
{
	private readonly FilesRepository $mediaRepository;
	private readonly NodesRepository $nodesRepository;
	private readonly AclValidator $aclValidator;

	private readonly LoggerInterface $logger;
	private int $UID;
	private string $pathOriginals;
	private string $pathPreviews;
	private string $pathThumbnails;

	/**
	 * @throws CoreException
	 */
	public function __construct(FilesRepository $mediaRepository, NodesRepository $nodesRepository, AclValidator $aclValidator, LoggerInterface $logger)
	{
		$this->mediaRepository = $mediaRepository;
		$this->nodesRepository = $nodesRepository;
		$this->aclValidator    = $aclValidator;
		$this->logger          = $logger;

		$this->pathOriginals = $aclValidator->getConfig()->getConfigValue('originals', 'mediapool', 'directories');
		$this->pathPreviews  = $aclValidator->getConfig()->getConfigValue('previews', 'mediapool', 'directories');
		$this->pathThumbnails = $aclValidator->getConfig()->getConfigValue('thumbnails', 'mediapool', 'directories');
	}

	public function setUID(int $UID): void
	{
		$this->UID = $UID;
	}

	public function getPathThumbnails(): string
	{
		return $this->pathThumbnails;
	}

	public function getPathOriginals(): string
	{
		return $this->pathOriginals;
	}

	public function listMedia(int $nodeId): array
	{
		try
		{
			$node = $this->nodesRepository->findNodeOwner($nodeId);

			$permissions = $this->aclValidator->checkDirectoryPermissions($this->UID, $node);
			if (!$permissions['read'])
				throw new ModuleException('mediapool', 'No read permissions on directory:'. $node['name']);

			$result = $this->mediaRepository->findAllByNodeId($nodeId);

			foreach ($result as &$media)
			{
				$media['metadata'] = json_decode($media['metadata'], true);

				$media['paths']['original']  = $this->getPathOriginals().'/'.$media['checksum'].'.'.$media['extension'];
				$media['paths']['preview']   = $this->pathPreviews.'/'.$media['checksum'].'.'.$media['extension'];
				$media['paths']['thumbnail'] = $this->getPathThumbnails().'/'.$media['checksum'].'.'.$media['thumb_extension'];
			}

			return $result;
		}
		catch (Exception | CoreException | ModuleException | PhpfastcacheSimpleCacheException | DatabaseException $e)
		{
			$this->logger->error('Error listing media: ' . $e->getMessage());
			return [];
		}
	}

	public function fetchMedia(string $mediaId): array
	{
		try
		{
			$media = $this->mediaRepository->findAllWithOwnerById($mediaId);

			// media has all the required fields (node_id, UID, company_id) for permissions check
			$permissions = $this->aclValidator->checkDirectoryPermissions($this->UID, $media);
			if (!$permissions['read'])
				throw new ModuleException('mediapool', 'No read permissions in this directory: '. $media['node_id']);

			$media['metadata'] = json_decode($media['metadata'], true);

			$media['paths']['original']  = $this->pathOriginals.'/'.$media['checksum'].'.'.$media['extension'];
			$media['paths']['preview']   = $this->pathPreviews.'/'.$media['checksum'].'.'.$media['extension'];
			$media['paths']['thumbnail'] = $this->pathThumbnails.'/'.$media['checksum'].'.'.$media['thumb_extension'];
			return $media;
		}
		catch (Exception | CoreException | ModuleException | PhpfastcacheSimpleCacheException $e)
		{
			$this->logger->error('Error fetch media: ' . $e->getMessage());
			return [];
		}
	}

	public function fetchMediaByChecksum($checksum): array
	{
		$media = $this->mediaRepository->findAllWithOwnerByCheckSum($checksum);
		$media['metadata'] = json_decode($media['metadata'], true);

		return $media;
	}

	public function updateMedia(string $mediaId, string $filename, string $description): int
	{
		try
		{
			$media = $this->mediaRepository->findAllWithOwnerById($mediaId);

			// media has all the required fields (node_id, UID, company_id) for permissions check
			$permissions = $this->aclValidator->checkDirectoryPermissions($this->UID, $media);
			if (!$permissions['edit'])
				throw new ModuleException('mediapool', 'No edit permissions in this directory: '. $media['node_id']);

			return $this->mediaRepository->update($mediaId, ['filename' => $filename, 'media_description' => $description]);
		}
		catch (Exception | CoreException | ModuleException | PhpfastcacheSimpleCacheException $e)
		{
			$this->logger->error('Error updating media: ' . $e->getMessage());
			return 0;
		}
	}

	public function deleteMedia(string $mediaId): int
	{
		try
		{
			$media = $this->mediaRepository->findAllWithOwnerById($mediaId);

			// media has all the required fields (node_id, UID, company_id) for permissions check
			$permissions = $this->aclValidator->checkDirectoryPermissions($this->UID, $media);
			if (!$permissions['edit'])
				throw new ModuleException('mediapool', 'No edit permissions in this directory: '. $media['node_id']);

			$field     = ['deleted' => 1];
			$condition = ['media_id' => $mediaId];

			return $this->mediaRepository->updateWithWhere($field, $condition);
		}
		catch (Exception | CoreException | ModuleException | PhpfastcacheSimpleCacheException $e)
		{
			$this->logger->error('Error deleting media: ' . $e->getMessage());
			return 0;
		}
	}

	public function moveMedia(string $mediaId, int $nodeId): int
	{
		try
		{
			$media = $this->mediaRepository->findAllWithOwnerById($mediaId);
			$permissions = $this->aclValidator->checkDirectoryPermissions($this->UID, $media);
			if (!$permissions['read'])
				throw new ModuleException('mediapool', 'No read permissions in this directory: '. $media['node_id']);

			$node = $this->nodesRepository->findNodeOwner($nodeId);
			$permissions = $this->aclValidator->checkDirectoryPermissions($this->UID, $node);
			if (!$permissions['edit'])
				throw new ModuleException('mediapool', 'No edit permissions in this directory: '. $node['name']);

			$condition = ['media_id' => $mediaId];
			$field     = ['node_id'  => $nodeId];

			return $this->mediaRepository->updateWithWhere($field, $condition);
		}
		catch (Exception | CoreException | ModuleException | DatabaseException | PhpfastcacheSimpleCacheException $e)
		{
			$this->logger->error('Error moving media: ' . $e->getMessage());
			return 0;
		}
	}

	public function cloneMedia(string $mediaId): array
	{
		try
		{
			$media = $this->mediaRepository->findAllWithOwnerById($mediaId);
			$permissions = $this->aclValidator->checkDirectoryPermissions($this->UID, $media);
			if (!$permissions['read'] || !$permissions['edit'])
				throw new ModuleException('mediapool', 'No permissions on this directory: '. $media['node_id']);

			$dataSet             = $this->mediaRepository->getFirstDataSet($this->mediaRepository->findById($mediaId));
			$dataSet['media_id'] = Uuid::uuid4()->toString();
			$dataSet['UID']      = $this->UID;

			$dataSet['uuid']     = $this->mediaRepository->insert($dataSet);

			return $dataSet;
		}
		catch (Exception | CoreException | ModuleException | PhpfastcacheSimpleCacheException $e)
		{
			$this->logger->error('Error cloning media: ' . $e->getMessage());
			return [];
		}
	}
}