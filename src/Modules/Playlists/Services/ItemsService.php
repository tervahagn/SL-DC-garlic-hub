<?php

namespace App\Modules\Playlists\Services;

use App\Framework\Services\AbstractBaseService;
use App\Modules\Playlists\Repositories\ItemsRepository;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

class ItemsService extends AbstractBaseService
{
	private readonly ItemsRepository $itemsRepository;
	private readonly PlaylistsRepository $playlistsRepository;
	private readonly AclValidator $aclValidator;

	/**
	 * @param ItemsRepository $itemssRepository
	 * @param PlaylistsRepository $playlistsRepository
	 * @param AclValidator $aclValidator
	 */
	public function __construct(ItemsRepository $itemsRepository, PlaylistsRepository $playlistsRepository, AclValidator $aclValidator, LoggerInterface $logger)
	{
		$this->itemsRepository = $itemsRepository;
		$this->playlistsRepository = $playlistsRepository;
		$this->aclValidator = $aclValidator;
		parent::__construct($logger);
	}

	/**
	 * @throws Exception
	 */
	public function insert(int $playlist_id, string $id, string $source): int
	{
		$saveData = [
			'playlist_id' => $playlist_id,
			'datasource' => 'file',
			'media_type' => 'image',
			'file_resource' => $id
		];
		return $this->itemsRepository->insert($saveData);
	}


}