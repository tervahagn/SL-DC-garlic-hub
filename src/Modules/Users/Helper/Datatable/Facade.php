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

namespace App\Modules\Users\Helper\Datatable;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Datatable\DatatableFacadeInterface;
use App\Framework\Utils\FormParameters\BaseFilterParametersInterface;
use App\Modules\Users\Services\UsersOverviewService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class Facade implements DatatableFacadeInterface
{

	private readonly DatatableBuilder $datatableBuilder;
	private readonly DatatablePreparer $datatableFormatter;
	private readonly Parameters $parameters;
	private readonly UsersOverviewService $usersService;
	private int $UID;
	private Translator $translator;

	public function __construct(DatatableBuilder $datatableBuilder, DatatablePreparer $datatableFormatter, Parameters $parameters, UsersOverviewService $usersService)
	{
		$this->datatableBuilder = $datatableBuilder;
		$this->datatableFormatter = $datatableFormatter;
		$this->parameters = $parameters;
		$this->usersService = $usersService;
	}
	public function configure(Translator $translator, Session $session): void
	{
		$this->UID = $session->get('user')['UID'];
		$this->usersService->setUID($this->UID);
		$this->translator = $translator;
	}

	/**
	 * @throws ModuleException
	 */
	public function processSubmittedUserInput(array $userInputs): void
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

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 * @throws Exception
	 */
	public function prepareUITemplate(): array
	{
		$this->datatableFormatter->configure($this->parameters);

		$datatableStructure = $this->datatableBuilder->getDatatableStructure();

		return [
			'filter_elements'     => $this->datatableFormatter->prepareFilterForm($datatableStructure['form']),
			'pagination_dropdown' => $this->datatableFormatter->preparePaginationDropDown($datatableStructure['dropdown']),
			'pagination_links'    => $this->datatableFormatter->preparePaginationLinks($datatableStructure['pager']),
			'has_add'			  => $this->datatableFormatter->prepareAdd('person-add'),
			'results_header'      => $this->datatableFormatter->prepareTableHeader($datatableStructure['header'],  ['users', 'main']),
			'results_list'        => $this->formatList($datatableStructure['header']),
			'results_count'       => $this->usersService->getCurrentTotalResult(),
			'title'               => $this->translator->translate('overview', 'users'),
			'template_name'       => 'users/datatable',
			'module_name'		  => 'users',
			'additional_css'      => ['/css/users/overview.css'],
			'footer_modules'      => ['/js/users/overview/init.js'],
			'sort'				  => [
				'column' => $this->parameters->getValueOfParameter(BaseFilterParametersInterface::PARAMETER_SORT_COLUMN),
				'order' =>  $this->parameters->getValueOfParameter(BaseFilterParametersInterface::PARAMETER_SORT_ORDER)
			],
			'page'      => [
				'current' => $this->parameters->getValueOfParameter(BaseFilterParametersInterface::PARAMETER_ELEMENTS_PAGE),
				'num_elements' => $this->parameters->getValueOfParameter(BaseFilterParametersInterface::PARAMETER_ELEMENTS_PER_PAGE),
			]
		];
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws Exception
	 * @throws FrameworkException
	 */
	private function formatList(array $fields): array
	{
		return $this->datatableFormatter->prepareTableBody(
			$this->usersService->getCurrentFilterResults(),
			$fields,
			$this->UID
		);
	}
}