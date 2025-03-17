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

namespace App\Modules\Playlists\Helper\Overview;

use App\Framework\Core\Session;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\DataGridFacadeInterface;
use App\Modules\Playlists\Services\PlaylistsService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class Facade implements DataGridFacadeInterface
{
	private readonly FormCreator $formCreator;
	private readonly Parameters $parameters;
	private readonly PlaylistsService $playlistsService;
	private readonly ResultsManager $resultsManager;
	private readonly TemplateRenderer $renderer;
	private int $UID;

	public function __construct(FormCreator $formCreator, Parameters $parameters, PlaylistsService  $playlistsService, ResultsManager $resultsManager, TemplateRenderer  $renderer)
	{
		$this->formCreator = $formCreator;
		$this->parameters = $parameters;
		$this->playlistsService = $playlistsService;
		$this->resultsManager = $resultsManager;
		$this->renderer = $renderer;
	}

	public function init(Session $session): void
	{
		$this->UID = $session->get('user')['UID'];
		$this->playlistsService->setUID($this->UID);
	}

	/**
	 * @throws ModuleException
	 */
	public function handleUserInput(array $userInputs): void
	{
		$this->parameters->setUserInputs($userInputs);
		$this->parameters->parseInputFilterAllUsers();

		$this->playlistsService->loadPlaylistsForOverview($this->parameters);
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
		$this->formCreator->collectFormElements();

		$this->resultsManager->createPagination($this->parameters, $this->playlistsService->getCurrentTotalResult());
		$this->resultsManager->createTableFields($this->UID);
		return $this;
	}

	/**
	 * @throws CoreException
	 * @throws Exception
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function renderDataGrid(): array
	{
		$datalistSections = [
			'filter_elements'     => $this->formCreator->renderForm(),
			'pagination_dropdown' => $this->resultsManager->renderPaginationDropDown(),
			'pagination_links'    => $this->resultsManager->renderPaginationLinks(),
			'results_header'      => $this->resultsManager->renderTableHeader($this->parameters),
			'results_body'        => $this->renderBody(),
			'results_count'       => $this->playlistsService->getCurrentTotalResult()
		];
		return $this->renderer->renderTemplate($datalistSections);
	}


	/**
	 * @return array
	 * @throws CoreException
	 * @throws Exception
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException ´
	 */
	private function renderBody(): array
	{
		$showedIds     = array_column($this->playlistsService->getCurrentFilterResults(), 'playlist_id');
		return $this->resultsManager->renderTableBody(
			$this->playlistsService->getCurrentFilterResults(),
			$this->playlistsService->getPlaylistsInUse($showedIds)
		);
	}

}