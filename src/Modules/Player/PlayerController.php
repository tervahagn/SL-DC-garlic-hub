<?php

namespace App\Modules\Player;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController
{
	#[Route('/player', name: 'player')]
	public function Player(): Response
	{
		return new Response('Welcome to the Player!');
	}

	#[Route('/player/edit', name: 'player_edit')]
	public function editPlayer(): Response
	{
		return new Response('Welcome to the Player!');
	}
}