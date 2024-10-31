<?php

namespace App\Modules\Playlist;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlaylistController
{
	#[Route('/playlists', name: 'playlist')]
	public function playlists(): Response
	{
		return new Response('Welcome to the playlist!');
	}

	#[Route('/playlists/edit', name: 'playlists_edit')]
	public function editPlaylist(): Response
	{
		return new Response('Welcome to the playlist!');
	}
}