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
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\DataGrid\BaseDataGridTemplateFormatter;
use App\Framework\Utils\DataGridFacadeInterface;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Modules\Playlists\Services\PlaylistsService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class Facade implements DataGridFacadeInterface
{
	private readonly DataGridBuilder $dataGridBuilder;
	private readonly DataGridFormatter $dataGridFormatter;
	private readonly Parameters $parameters;
	private readonly PlaylistsService $playlistsService;
	private readonly BaseDataGridTemplateFormatter $renderer;
	private int $UID;
	private Translator $translator;

	public function __construct(DataGridBuilder $dataGridBuilder, DataGridFormatter $dataGridFormatter, Parameters $parameters, PlaylistsService $playlistsService, BaseDataGridTemplateFormatter $renderer)
	{
		$this->dataGridBuilder = $dataGridBuilder;
		$this->dataGridFormatter = $dataGridFormatter;
		$this->parameters = $parameters;
		$this->playlistsService = $playlistsService;

		$this->renderer = $renderer;
	}

	public function configure(Translator $translator, Session $session): void
	{
		$this->UID = $session->get('user')['UID'];
		$this->playlistsService->setUID($this->UID);
		$this->translator = $translator;
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
		$this->dataGridBuilder->collectFormElements();
		$this->dataGridBuilder->createDropDown($this->playlistsService->getCurrentTotalResult());
		$this->dataGridBuilder->createPagination($this->playlistsService->getCurrentTotalResult());
		$this->dataGridBuilder->createTableFields();

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
	public function prepareDataGridTemplate(): array
	{
		$this->dataGridFormatter->configurePagination($this->parameters);

		$dataGridBuild = $this->dataGridBuilder->getDataGridBuild();

		$datalistSections = [
			'filter_elements'     => $this->dataGridFormatter->formatFilterForm($dataGridBuild['form']),
			'pagination_dropdown' => $this->dataGridFormatter->formatPaginationDropDown($dataGridBuild['dropdown']),
			'pagination_links'    => $this->dataGridFormatter->formatPaginationLinks($dataGridBuild['pager']),
			'has_add'			  => $this->dataGridFormatter->formatAdd(),
			'results_header'      => $this->dataGridFormatter->formatTableHeader($this->parameters, $dataGridBuild['header']),
			'results_list'        => $this->formatList($dataGridBuild['header']),
			'results_count'       => $this->playlistsService->getCurrentTotalResult(),
			'title'               => $this->translator->translate('overview', 'playlists'),
			'template_name'       => 'playlists/overview',
			'module_name'		  => 'playlists',
			'additional_css'      => ['/css/playlists/overview.css'],
			'footer_modules'      => ['/js/playlists/overview/init.js'],
			'sort'				  => [
				'column' => $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_SORT_COLUMN),
				'order' =>  $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_SORT_ORDER)
			],
			'page'      => [
				'current' => $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PAGE),
				'num_elements' => $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE),
			]
		];
		$templateData = $this->renderer->formatUITemplate($datalistSections);
		$templateData['this_layout']['data']['create_playlist_contextmenu'] = $this->dataGridFormatter->formatPlaylistContextMenu();

		return $templateData;
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
	private function formatList(array $fields): array
	{
		$showedIds     = array_column($this->playlistsService->getCurrentFilterResults(), 'playlist_id');
		return $this->dataGridFormatter->formatTableBody(
			$this->playlistsService->getCurrentFilterResults(),
			$fields,
			$this->playlistsService->getPlaylistsInUse($showedIds),
			$this->UID
		);
	}

}