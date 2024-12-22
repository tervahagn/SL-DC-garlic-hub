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


namespace App\Modules\Mediapool\Controller;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;

class ShowController
{
	/**
	 * @throws Exception|InvalidArgumentException
	 */
	public function show(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$translator = $request->getAttribute('translator');

		$data = [
			'main_layout' => [
				'LANG_PAGE_TITLE' => $translator->translate('mediapool', 'menu'),
				'additional_css' => ['/css/external/bootstrap-icons.min.css', '/css/external/wunderbaum.min.css', '/css/mediapool/overview.css'],
				'footer_scripts' => ['/js/external/wunderbaum.umd.min.js', '/js/mediapool/treeview.js'],
				'footer_modules' => ['MODULE_NAME' => 'wunderbaum', 'MODULE_PATH' => 'https://cdn.jsdelivr.net/npm/wunderbaum@0/+esm']
			],
			'this_layout' => [
				'template' => 'mediapool/overview', // Template-name
				'data' => [

				]
			]
		];
		$response->getBody()->write(serialize($data));

		return $response->withHeader('Content-Type', 'text/html');
	}

}