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

namespace App\Modules\Users\Helper\Overview;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\Datatable\DatatableFacadeInterface;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Modules\Users\Services\UsersOverviewService;

class Facade implements DatatableFacadeInterface
{

	private readonly DatatableBuilder $datatableBuilder;
	private readonly DatatableFormatter $datatableFormatter;
	private readonly Parameters $parameters;
	private readonly UsersOverviewService $usersService;
	private int $UID;
	private Translator $translator;
	private DatatableFormatter $dataGridFormatter;

	public function __construct(DatatableBuilder $datatableBuilder, DatatableFormatter $datatableFormatter, Parameters $parameters, UsersOverviewService $usersService)
	{
		$this->datatableBuilder = $datatableBuilder;
		$this->dataGridFormatter = $datatableFormatter;
		$this->parameters = $parameters;
		$this->usersService = $usersService;
	}
	public function configure(Translator $translator, Session $session): void
	{
		$this->UID = $session->get('user')['UID'];
		$this->usersService->setUID($this->UID);
		$this->translator = $translator;
	}

	public function handleUserInput(array $userInputs): void
	{
		$this->parameters->setUserInputs($userInputs);
		$this->parameters->parseInputFilterAllUsers();
		$this->usersService->loadUsersForOverview($this->parameters);
	}

	public function prepareDataGrid(): static
	{
		$this->datatableBuilder->collectFormElements();
		$this->datatableBuilder->createPagination($this->usersService->getCurrentTotalResult());
		$this->datatableBuilder->createDropDown();
		$this->datatableBuilder->createTableFields();

		return $this;
	}

	public function prepareTemplate(): array
	{
		$this->dataGridFormatter->configurePagination($this->parameters);

		$dataGridBuild = $this->datatableBuilder->getDataGridBuild();

		return [
			'filter_elements'     => $this->dataGridFormatter->formatFilterForm($dataGridBuild['form']),
			'pagination_dropdown' => $this->dataGridFormatter->formatPaginationDropDown($dataGridBuild['dropdown']),
			'pagination_links'    => $this->dataGridFormatter->formatPaginationLinks($dataGridBuild['pager']),
			'has_add'			  => $this->dataGridFormatter->formatAdd(),
			'results_header'      => $this->dataGridFormatter->formatTableHeader($this->parameters, $dataGridBuild['header']),
			'results_list'        => $this->formatList($dataGridBuild['header']),
			'results_count'       => $this->usersService->getCurrentTotalResult(),
			'title'               => $this->translator->translate('overview', 'playlists'),
			'template_name'       => 'users/overview',
			'module_name'		  => 'users',
			'additional_css'      => ['/css/users/overview.css'],
			'footer_modules'      => ['/js/users/overview/init.js'],
			'sort'				  => [
				'column' => $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_SORT_COLUMN),
				'order' =>  $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_SORT_ORDER)
			],
			'page'      => [
				'current' => $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PAGE),
				'num_elements' => $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE),
			]
		];
	}

	private function formatList(array $fields): array
	{
		$showedIds     = array_column($this->usersService->getCurrentFilterResults(), 'playlist_id');
		return $this->dataGridFormatter->formatTableBody(
			$this->usersService->getCurrentFilterResults(),
			$fields,
			$this->UID
		);
	}
}