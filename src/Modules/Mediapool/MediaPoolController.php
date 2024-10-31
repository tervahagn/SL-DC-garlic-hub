<?php

namespace App\Modules\Mediapool;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MediaPoolController
{
	#[Route('/mediapool/', name: 'mediapool')]
	public function home(): Response
	{
		return new Response('Welcome to the mediapool!');
	}

}