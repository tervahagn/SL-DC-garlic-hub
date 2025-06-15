<?php

namespace App\Modules\Playlists\Services;

use App\Modules\Player\Repositories\PlayerRepository;
use App\Modules\Playlists\Repositories\ItemsRepository;
use Doctrine\DBAL\Exception;

// use App\Modules\Playlists\Repositories\ChannelRepository;

readonly class PlaylistUsageService
{
    private PlayerRepository $playerRepository;
    private ItemsRepository $itemsRepository;
    // private readonly ChannelRepository $channelRepository;

    public function __construct(PlayerRepository $playerRepository, ItemsRepository $itemsRepository /*ChannelRepository $channelRepository*/)
	{
        $this->playerRepository = $playerRepository;
        $this->itemsRepository  = $itemsRepository;
        // $this->channelRepository = $channelRepository;
    }

	/**
	 * @param int[] $playlistIds
	 * @return array<string,mixed>
	 * @throws Exception
	 */
	public function determinePlaylistsInUse(array $playlistIds): array
    {
        $results = [];
        
        foreach($this->playerRepository->findPlaylistIdsByPlaylistIds($playlistIds) as $value)
		{
            $results[$value['playlist_id']] = true;
        }
        
        foreach($this->itemsRepository->findFileResourcesByPlaylistId($playlistIds) as $value)
		{
            $results[$value['playlist_id']] = true;
        }

        /* no channels currently
        foreach($this->channelRepository->findTableIdsByPlaylistIds($playlistIds) as $value) {
            $results[$value['table_id']] = true;
        }
        */

        return $results;
    }
}