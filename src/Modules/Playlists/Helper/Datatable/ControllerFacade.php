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

namespace App\Modules\Playlists\Helper\Datatable;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Datatable\DatatableFacadeInterface;
use App\Modules\Playlists\Services\PlaylistsService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class ControllerFacade implements DatatableFacadeInterface
{
	private readonly DatatableBuilder $datatableBuilder;
	private readonly DatatablePreparer $datatableFormatter;
	private readonly PlaylistsService $playlistsService;
	private int $UID;

	public function __construct(DatatableBuilder $datatableBuilder, DatatablePreparer $datatableFormatter, PlaylistsService $playlistsService)
	{
		$this->datatableBuilder = $datatableBuilder;
		$this->datatableFormatter = $datatableFormatter;
		$this->playlistsService = $playlistsService;
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws Exception
	 */
	public function configure(Translator $translator, Session $session): void
	{
		$this->UID = $session->get('user')['UID'];
		$this->playlistsService->setUID($this->UID);
		$this->datatableBuilder->configureParameters($this->UID);
		$this->datatableFormatter->setTranslator($translator);
		$this->datatableBuilder->setTranslator($translator);
	}

	/**
	 * @throws ModuleException
	 */
	public function processSubmittedUserInput(): void
	{
		$this->datatableBuilder->determineParameters();
		$this->playlistsService->loadPlaylistsForOverview();
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws Exception
	 * @throws FrameworkException
	 */
	public function prepareDataGrid(): static
	{
		$this->datatableBuilder->buildTitle();
		$this->datatableBuilder->collectFormElements();
		$this->datatableBuilder->createPagination($this->playlistsService->getCurrentTotalResult());
		$this->datatableBuilder->createDropDown();
		$this->datatableBuilder->createTableFields();

		return $this;
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws CoreException
	 */
	public function prepareContextMenu(): array
	{
		return $this->datatableFormatter->formatPlaylistContextMenu();
	}

	/**
	 * @throws CoreException
	 * @throws Exception
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function prepareUITemplate(): array
	{
		$datatableStructure = $this->datatableBuilder->getDatatableStructure();
		$pagination         = $this->datatableFormatter->preparePagination($datatableStructure['pager'], $datatableStructure['dropdown']);

		return [
			'filter_elements'     => $this->datatableFormatter->prepareFilterForm($datatableStructure['form']),
			'pagination_dropdown' => $pagination['dropdown'],
			'pagination_links'    => $pagination['links'],
			'has_add'			  => $this->datatableFormatter->prepareAdd(),
			'results_header'      => $this->datatableFormatter->prepareTableHeader($datatableStructure['header'], ['playlists', 'main']),
			'results_list'        => $this->prepareList($datatableStructure['header']),
			'results_count'       => $this->playlistsService->getCurrentTotalResult(),
			'title'               => $datatableStructure['title'],
			'template_name'       => 'playlists/datatable',
			'module_name'		  => 'playlists',
			'additional_css'      => ['/css/playlists/overview.css'],
			'footer_modules'      => ['/js/playlists/overview/init.js'],
			'sort'				  => $this->datatableFormatter->prepareSort(),
			'page'      		  => $this->datatableFormatter->preparePage()
		];
	}

	/**
	 * @param array $fields
	 * @return array
	 * @throws CoreException
	 * @throws Exception
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException ´
	 */
	private function prepareList(array $fields): array
	{
		$showedIds = array_column($this->playlistsService->getCurrentFilterResults(), 'playlist_id');
		$this->datatableFormatter->setUsedPlaylists($this->playlistsService->getPlaylistsInUse($showedIds));

		return $this->datatableFormatter->prepareTableBody(
			$this->playlistsService->getCurrentFilterResults(),
			$fields,
			$this->UID
		);
	}

}