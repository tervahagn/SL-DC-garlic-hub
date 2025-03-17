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
use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\FilteredList\Paginator\PaginationManager;
use App\Modules\Playlists\Services\PlaylistsService;

class Facade
{
	private readonly FormCreator $formCreator;
	private readonly Parameters $parameters;
	private readonly PlaylistsService $playlistsService;
	private readonly PaginationManager $paginatorService;
	private readonly ResultsList $resultsList;
	private readonly TemplateRenderer $renderer;
	private Translator $translator;
	private int $UID;

	public function __construct(FormCreator $formCreator,
								Parameters $parameters,
								PlaylistsService $playlistsService,
								PaginationManager $paginatorService,
								ResultsList $resultsList,
								TemplateRenderer $renderer
	)
	{
		$this->formCreator = $formCreator;
		$this->parameters = $parameters;
		$this->playlistsService = $playlistsService;
		$this->paginatorService = $paginatorService;
		$this->resultsList = $resultsList;
		$this->renderer = $renderer;
	}

	public function init(Translator $translator, Session $session): void
	{
		$this->formCreator->init($translator, $session);
		$this->UID = $session->get('user')['UID'];
		$this->playlistsService->setUID($this->UID);
		$this->translator = $translator;
	}

	Public function handleUserInput(array $userInputs): void
	{
		$this->parameters->setUserInputs($userInputs);
		$this->parameters->parseInputFilterAllUsers();

		$this->playlistsService->loadPlaylistsForOverview($this->parameters);
	}

	public function prepareDataGrid(): static
	{
		$this->formCreator->collectFormElements();

		$this->resultsList->createTableFields($this->UID);
		$this->paginatorService->init($this->parameters, 'playlists')
			->createPagination($this->playlistsService->getCurrentTotalResult())
			->createDropDown();

		return $this;
	}

	public function render()
	{

		$datalistSections = [
			'filter_elements' => $this->formCreator->renderForm(),
			'pagination_dropdown' => $this->paginatorService->renderPaginationDropDown(),
			'pagination_links' => $this->paginatorService->renderPaginationLinks('playlists'),
			'results_header'    => $this->resultsList->renderTableHeader(),
			'results_body'     => $this->renderBody(),
			'results_count'    => $this->playlistsService->getCurrentTotalResult()
		];
		return $this->renderer->renderTemplate($datalistSections);

	}


	private function renderBody(): array
	{
		$showedIds     = array_column($this->playlistsService->getCurrentFilterResults(), 'playlist_id');;
		$this->resultsList->setCurrentTotalResult($this->playlistsService->getCurrentTotalResult());
		$this->resultsList->setCurrentFilterResults($this->playlistsService->getCurrentFilterResults());
		return $this->resultsList->renderTableBody(
			$this->translator,
			$showedIds,
			$this->playlistsService->getPlaylistsInUse($showedIds)
		);
	}

}