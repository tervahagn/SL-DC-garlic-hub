<?php

namespace App\Modules\Playlists\Services;

use App\Modules\Player\Repositories\PlayerRepository;
use App\Modules\Playlists\Repositories\ItemsRepository;
// use App\Modules\Playlists\Repositories\ChannelRepository;

class PlaylistUsageService
{
    private readonly PlayerRepository $playerRepository;
    private readonly ItemsRepository $itemsRepository;
    // private readonly ChannelRepository $channelRepository;

    public function __construct(PlayerRepository $playerRepository, ItemsRepository $itemsRepository /*ChannelRepository $channelRepository*/)
	{
        $this->playerRepository = $playerRepository;
        $this->itemsRepository  = $itemsRepository;
        // $this->channelRepository = $channelRepository;
    }

    public function determinePlaylistsInUse(array $playlistIds): array
    {
        return $this->checkPlaylistsUsage($playlistIds);
    }

    protected function checkPlaylistsUsage(array $playlistIds): array
    {
        $results = [];
        
        foreach($this->playerRepository->findPlaylistIdsByPlaylistIds($playlistIds) as $value)
		{
            $results[$value['playlist_id']] = true;
        }
        
        foreach($this->itemsRepository->findFileResourcesByPlaylistId($playlistIds) as $value) {
            $results[$value['media_id']] = true;
        }

        /* no channels currently
        foreach($this->channelRepository->findTableIdsByPlaylistIds($playlistIds) as $value) {
            $results[$value['table_id']] = true;
        }
        */

        return $results;
    }
}