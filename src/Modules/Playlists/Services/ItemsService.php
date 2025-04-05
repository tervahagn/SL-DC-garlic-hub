<?php

namespace App\Modules\Playlists\Services;

use App\Framework\Exceptions\ModuleException;
use App\Framework\Services\AbstractBaseService;
use App\Modules\Mediapool\Services\MediaService;
use App\Modules\Playlists\Repositories\ItemsRepository;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

class ItemsService extends AbstractBaseService
{
	CONST int DURATION_DEFAULT = 15;
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
	public function insert(int $playlistId, string $id, string $source): int
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

			$saveItem = [
				'playlist_id'   => $playlistId,
				'datasource'    => 'file',
				'item_duration' => $media['duration'] ?? self::DURATION_DEFAULT,
				'item_filesize' => $media['metadata']['size'],
				'item_type'     => 'media',
				'file_resource' => $media['checksum'],
				'mimetype'      => $media['mimetype'],
			];
			$id = $this->itemsRepository->insert($saveItem);

			$this->updatePlaylistDurationAndFileSize($playlistData);

			$this->itemsRepository->commitTransaction();
			return $id;
		}
		catch (Exception | ModuleException $e)
		{
			$this->itemsRepository->rollBackTransaction();
			$this->logger->error('Error insert media: ' . $e->getMessage());
			return 0;
		}
	}

	private function updatePlaylistDurationAndFileSize(array $playlistData)
	{
		if (empty($playlistData) || !isset($playlistData['playlist_id']))
			return $this;

		$this->durationCalculatorService->calculatePlaylistDurationFromItems($playlistData);
		$this->durationCalculatorService->calculatePlaylistFilesizeFromItems($playlistData['playlist_id']);

		$savePlaylist = array(
			'filesize'          => $this->durationCalculatorService->getFileSize(),
			'duration'          => $this->durationCalculatorService->getDuration(),
			'owner_duration'    => $this->durationCalculatorService->getOwnerDuration()
		);
		$this->playlistsService->update($playlistData['playlist_id'], $savePlaylist); // update playlist durations in table


		//now update all higher level playlists
		$saveItem = array(
			'item_duration'     => $this->durationCalculatorService->getDuration(),
			'item_filesize'     => $this->durationCalculatorService->getFileSize()
		);
		$this->itemsRepository->update($playlistData['playlist_id'], $saveItem);

		// find all playlist which have inserted this playlist
		$tmp = $this->playlistsService->findAllByItemsAsPlaylistAndMediaId($playlistData['playlist_id']);
		foreach($tmp as $values) // recurse all playlist which have this playlist as item for updating durations
		{
			$this->updatePlaylistDurationAndFileSize($values);
		}

		return $this;
	}

	private function allowedByTimeLimit(int $playlistId, int $timeLimit): bool
	{
		if ($timeLimit > 0)
			return ($this->itemsRepository->sumDurationOfItemsByUIDAndPlaylistId($this->UID, $playlistId) <= $timeLimit);

		return true;
	}


}