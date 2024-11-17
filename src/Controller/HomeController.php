<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
class HomeController
{
	public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$session = $request->getAttribute('session');
		if (!$session->exists('user'))
			return $response->withHeader('Location', '/login')->withStatus(302);

		$data = [
			'main_layout' => [
				'LANG_PAGE_TITLE' => 'Garlic Hub - Home'
			],
			'this_layout' => [
				'template' => 'home', // Template name
				'data' => [
					'LANG_PAGE_HEADER' => 'Welcome',
					'LANG_CONTENT' => 'Yes! This is our starting homepage. And I know is is pretty useless to welcome people here. But hey, it is a start. So, do not overestimate it. At the end it is some more entertaining than this boring Lorem Ipsum text. So, enjoy your stay!',
					'SHOW_SESSION' => print_r($session->get('user'), true)

				]
			]
		];

		$response->getBody()->write(serialize($data));

		return $response->withHeader('Content-Type', 'text/html');
	}

}