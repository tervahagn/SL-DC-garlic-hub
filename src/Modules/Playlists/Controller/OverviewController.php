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

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FilteredList\Paginator\PaginatorService;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Modules\Playlists\FormHelper\FilterFormBuilder;
use App\Modules\Playlists\FormHelper\FilterParameters;
use App\Modules\Playlists\Services\PlaylistsOverviewService;
use App\Modules\Playlists\Services\ResultList;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Slim\Flash\Messages;

class OverviewController
{
	private readonly FilterFormBuilder $formBuilder;
	private readonly FilterParameters $parameters;
	private readonly PlaylistsOverviewService $playlistsService;
	private readonly PaginatorService $paginatorService;
	private readonly ResultList $resultList;

	private Translator $translator;
	private Session $session;
	private Messages $flash;

	public function __construct(FilterFormBuilder $formBuilder, FilterParameters $parameters, PlaylistsOverviewService $playlistsService, PaginatorService $paginatorService, ResultList $resultList)
	{
		$this->formBuilder      = $formBuilder;
		$this->parameters       = $parameters;
		$this->playlistsService = $playlistsService;
		$this->paginatorService = $paginatorService;
		$this->resultList       = $resultList;
	}

	/**
	 * @throws CoreException
	 * @throws Exception
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws ModuleException
	 */
	public function show(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$this->setImportantAttributes($request);

		$this->parameters->setUserInputs($request->getParsedBody() ?? []);
		$this->parameters->parseInputFilterAllUsers();
		$this->playlistsService->loadPlaylistsForOverview($this->parameters);
		$filter = array_combine(
			$this->parameters->getInputParametersKeys(),
			$this->parameters->getInputValuesArray()
		);
		$data = $this->buildForm([]);

		$response->getBody()->write(serialize($data));
		return $response->withHeader('Content-Type', 'text/html');
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	private function buildForm(array $filter): array
	{
		$elements = $this->formBuilder->init($this->translator, $this->session)->buildForm($filter);

		$title = $this->translator->translate('overview', 'playlists');
		$total = $this->playlistsService->getCurrentTotalResult();
		$this->paginatorService->setBaseFilter($this->parameters)->create($total);

		return [
			'main_layout' => [
				'LANG_PAGE_TITLE' => $title,
				'additional_css' => ['/css/playlists/overview.css'],
				'footer_modules' => ['/js/playlists/overview/init.js']
			],
			'this_layout' => [
				'template' => 'playlists/overview', // Template-name
				'data' => [
					'LANG_PAGE_HEADER' => $title,
					'FORM_ACTION' => '/playlists',
					'element_hidden' => $elements['hidden'],
					'form_element' => $elements['visible'],
					'LANG_ELEMENTS_FILTER' => $this->translator->translate('filter', 'main'),
					'SORT_COLUMN' => $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_SORT_COLUMN),
					'SORT_ORDER' =>  $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_SORT_ORDER),
					'ELEMENTS_PAGE', $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PAGE),
					'form_button' => [
						[
							'ELEMENT_BUTTON_TYPE' => 'submit',
							'ELEMENT_BUTTON_NAME' => 'submit',
						]
					],
					'create_playlist_contextmenu' => $this->buildPlaylistContextMenu(),
					'elements_per_page' => $this->paginatorService->renderElementsPerSiteDropDown(10,100,10),
					'add_allowed' => [
						'LANG_ELEMENTS_ADD_LINK' =>	$this->translator->translate('add', 'playlists'),
						'ELEMENTS_ADD_LINK' => '#'

					],
					'LANG_ELEMENTS_PER_PAGE' => $this->translator->translate('elements_per_page', 'main'),
					'LANG_COUNT_SEARCH_RESULTS' => sprintf($this->translator->translateWithPlural('count_search_results', 'playlists', $total), $total),
					'elements_pager' => $this->paginatorService->renderPagination('playlists'),
					'elements_result_header' => $this->renderHeader(),
					'elements_results' => $this->renderBody()

				]
			]
		];
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 * @throws Exception
	 */
	private function renderHeader()
	{
		$this->resultList->createFields($this->session->get('user')['UID']);
		return $this->resultList->renderTableHeader($this->parameters, 'playlists', $this->translator);
	}

	private function renderBody()
	{
		$showedIds     = $this->playlistsService->getPlaylistIdsFromResultSet();
		$usedPlaylists = $this->playlistsService->getPlaylistsInUse($showedIds);

		return $this->resultList->renderTableBody(
			$this->playlistsService->getCurrentFilterResults(),
			$showedIds,
			$usedPlaylists
		);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws CoreException
	 */
	private function buildPlaylistContextMenu(): array
	{
		$list = $this->translator->translateArrayForOptions('playlist_mode_selects', 'playlists');
		$data = [];
		foreach ($list as $key => $value)
		{
			$data[] = [
				'CREATE_PLAYLIST_MODE' => $key,
				'LANG_CREATE_PLAYLIST_MODE' => $value
			];
		}
		return $data;
	}

	private function setImportantAttributes(ServerRequestInterface $request): void
	{
		$this->translator = $request->getAttribute('translator');
		$this->session    = $request->getAttribute('session');
		$this->playlistsService->setUID($this->session->get('user')['UID']);
		$this->flash      = $request->getAttribute('flash');
	}

}