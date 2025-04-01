<?php

namespace App\Modules\Playlists\Services;

use App\Framework\Services\AbstractBaseService;
use App\Modules\Playlists\Repositories\ItemsRepository;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
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




}