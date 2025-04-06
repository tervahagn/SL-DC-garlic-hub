<?php

namespace App\Modules\Playlists\Services;

use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Services\AbstractBaseService;
use App\Modules\Mediapool\Services\MediaService;
use App\Modules\Playlists\Repositories\ItemsRepository;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Log\LoggerInterface;

class ItemsService extends AbstractBaseService
{
	private readonly ItemsRepository $itemsRepository;
	private readonly PlaylistsService $playlistsService;
	private readonly MediaService $mediaService;#
	private readonly DurationCalculatorService $durationCalculatorService;


	public function __construct(ItemsRepository $itemsRepository, MediaService $mediaService, PlaylistsService $playlistsService, DurationCalculatorService $durationCalculatorService, LoggerInterface $logger)
	{
		$this->itemsRepository  = $itemsRepository;
		$this->playlistsService = $playlistsService;
		$this->mediaService     = $mediaService;
		$this->durationCalculatorService = $durationCalculatorService;

		parent::__construct($logger);
	}

	/**
	 * @throws Exception
	 */
	public function loadItemsByPlaylistId(int $playlistId): array
	{
		$this->playlistsService->setUID($this->UID);

		$items = [];
		$thumbnailPath  = $this->mediaService->getPathTumbnails();
		foreach($this->itemsRepository->findAllByPlaylistId($playlistId) as $value)
		{
			$tmp = $value;

			$tmp['paths']['thumbnail'] = $thumbnailPath.'/'.$value['file_resource'].'.jpg';

			$items[] = $tmp;
		}

		// playlistService checks rights on the playlist
		return [
			'playlist' =>  $this->playlistsService->loadPlaylistForEdit($playlistId),
			'items' => $items
		];
	}


	/**
	 * @throws Exception
	 */
	public function insert(int $playlistId, string $id, string $source): array
	{
		try
		{
			$this->itemsRepository->beginTransaction();
			$this->mediaService->setUID($this->UID);
			$this->playlistsService->setUID($this->UID);
			$this->durationCalculatorService->setUID($this->UID);

			$media = $this->mediaService->fetchMedia($id); // checks rights, too
			if (empty($media))
				throw new ModuleException('items', 'Media is not accessible');

			$playlistData = $this->playlistsService->loadPlaylistForEdit($playlistId); // also checks rights
			if (empty($playlistData))
				throw new ModuleException('items', 'Playlist is not accessible');

			if (!$this->allowedByTimeLimit($playlistId, $playlistData['time_limit']))
				throw new ModuleException('items', 'Playlist time limit exceeds');

			$itemDuration =  $this->durationCalculatorService->calculateRemainingItemDuration($playlistData, $media);
			$saveItem = [
				'playlist_id'   => $playlistId,
				'datasource'    => 'file',
				'item_duration' => $itemDuration,
				'item_filesize' => $media['metadata']['size'],
				'item_name'     => $media['filename'],
				'item_type'     => 'media',
				'file_resource' => $media['checksum'],
				'mimetype'      => $media['mimetype'],
			];
			$id = $this->itemsRepository->insert($saveItem);
			if ($id === 0)
				throw new ModuleException('items', 'Playlist item could not inserted.');

			$saveItem['item_id'] = $id;

			$this->updatePlaylistDurationAndFileSize($playlistData);

			$this->calculateDurations($playlistData); // one time, because of the recursive calls in updatePlaylistDurationAndFileSize
			$saveItem = array_merge($saveItem, [
				'playlist_filesize'          => $this->durationCalculatorService->getFileSize(),
				'playlist_duration'          => $this->durationCalculatorService->getDuration(),
				'playlist_owner_duration'    => $this->durationCalculatorService->getOwnerDuration(),
				'paths'                      => $media['paths']
			]);

			$this->itemsRepository->commitTransaction();

			return $saveItem;
		}
		catch (Exception | ModuleException | CoreException | PhpfastcacheSimpleCacheException $e)
		{
			$this->itemsRepository->rollBackTransaction();
			$this->logger->error('Error insert media: ' . $e->getMessage());
			return [];
		}
	}

	private function updatePlaylistDurationAndFileSize(array $playlistData)
	{
		if (empty($playlistData) || !isset($playlistData['playlist_id']))
			return $this;

		$this->calculateDurations($playlistData);

		$savePlaylist = [
			'filesize'          => $this->durationCalculatorService->getFileSize(),
			'duration'          => $this->durationCalculatorService->getDuration(),
			'owner_duration'    => $this->durationCalculatorService->getOwnerDuration()
		];
		$this->playlistsService->update($playlistData['playlist_id'], $savePlaylist); // update playlist durations in table


		//now update all higher level playlists
		$saveItem = [
			'item_duration'     => $this->durationCalculatorService->getDuration(),
			'item_filesize'     => $this->durationCalculatorService->getFileSize()
		];
		$this->itemsRepository->update($playlistData['playlist_id'], $saveItem);

		// find all playlist which have inserted this playlist
		$tmp = $this->playlistsService->findAllByItemsAsPlaylistAndMediaId($playlistData['playlist_id']);
		foreach($tmp as $values) // recurse all playlist which have this playlist as item for updating durations
		{
			$this->updatePlaylistDurationAndFileSize($values);
		}
		return $this;
	}

	private function calculateDurations(array $playlistData): void
	{
		$this->durationCalculatorService->calculatePlaylistDurationFromItems($playlistData);
		$this->durationCalculatorService->calculatePlaylistFilesizeFromItems($playlistData['playlist_id']);

	}

	private function allowedByTimeLimit(int $playlistId, int $timeLimit): bool
	{
		if ($timeLimit > 0)
			return ($this->itemsRepository->sumDurationOfItemsByUIDAndPlaylistId($this->UID, $playlistId) <= $timeLimit);

		return true;
	}



}