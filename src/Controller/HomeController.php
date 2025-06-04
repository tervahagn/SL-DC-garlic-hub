<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace App\Controller;

use App\Framework\Core\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController
{

	public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$session = $request->getAttribute('session');
		$data = $this->generateHomePageData($session);
		$this->writeResponseData($response, $data);

		return $this->setContentType($response);
	}

	public function legals(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$data = [
			'main_layout' => [
				'LANG_PAGE_TITLE' => 'Garlic Hub - Website Legal Notice',
			],
			'this_layout' => [
				'template' => 'legals/legals',
				'data' => []
			]
		];

		$this->writeResponseData($response, $data);
		return $this->setContentType($response);
	}


	private function generateHomePageData(Session $session): array
	{
		return [
			'main_layout' => [
				'LANG_PAGE_TITLE' => 'Garlic Hub - Home',
			],
			'this_layout' => [
				'template' => 'home',
				'data' => [
					'LANG_PAGE_HEADER' => 'Welcome',
					'LANG_CONTENT' => 'Yes! This is our starting homepage. And I know is is pretty useless to welcome people here. But hey, it is a start. So, do not overestimate it. At the end it is some more entertaining than this boring Lorem Ipsum text. So, enjoy your stay!',
					'SHOW_SESSION' => print_r($session->get('user'), true),
				],
			],
		];
	}

	private function writeResponseData(ResponseInterface $response, array $data): void
	{
		$response->getBody()->write(serialize($data));
	}

	private function setContentType(ResponseInterface $response): ResponseInterface
	{
		return $response->withHeader('Content-Type', 'text/html');
	}

}