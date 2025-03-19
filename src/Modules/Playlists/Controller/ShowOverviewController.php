<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

namespace App\Modules\Playlists\Controller;

use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Datatable\BaseDataGridTemplateFormatter;
use App\Framework\Utils\Datatable\DatatableFacadeInterface;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;

readonly class ShowOverviewController
{
	private DatatableFacadeInterface $facade;
	private BaseDataGridTemplateFormatter $templateFormatter;
	public function __construct(DatatableFacadeInterface $facade, BaseDataGridTemplateFormatter $templateFormatter)
	{
		$this->facade            = $facade;
		$this->templateFormatter = $templateFormatter;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @return ResponseInterface
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function show(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$this->facade->configure($request->getAttribute('translator'), $request->getAttribute('session'));
		$this->facade->handleUserInput($_GET);

		$this->facade->prepareDataGrid();
		$dataGrid = $this->facade->prepareTemplate();

		$templateData = $this->templateFormatter->formatUITemplate($dataGrid);
		$templateData['this_layout']['data']['create_playlist_contextmenu'] = $this->facade->prepareContextMenu();

		$response->getBody()->write(serialize($templateData));

		return $response->withHeader('Content-Type', 'text/html');
	}

}