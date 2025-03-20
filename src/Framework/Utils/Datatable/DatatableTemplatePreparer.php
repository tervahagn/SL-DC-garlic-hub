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

namespace App\Framework\Utils\Datatable;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class DatatableTemplatePreparer
 *
 * This class is responsible for formatting data into a structured template for use in a datatable functionality.
 * It processes various settings and properties of the $datalistSections array and transforms them into
 * layout-specific arrays suitable for rendering UI elements.
 *
 * @see templates/generic/datatable.mustache
 */
class DatatableTemplatePreparer
{

	private Translator $translator;

	public function __construct(Translator $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function preparerUITemplate(array $datalistSections): array
	{
		return [
			'main_layout' => [
				'LANG_PAGE_TITLE' => $datalistSections['title'],
				'additional_css'  => $datalistSections['additional_css'],
				'footer_modules'   => $datalistSections['footer_modules']
			],
			'this_layout' => [
				'template' => $datalistSections['template_name'],
				'data' => [
					'LANG_PAGE_HEADER'     => $datalistSections['title'],
					'FORM_ACTION'          => '/'. $datalistSections['module_name'],
					'element_hidden'       => $datalistSections['filter_elements']['hidden'],
					'form_element'         => $datalistSections['filter_elements']['visible'],
					'LANG_ELEMENTS_FILTER' => $this->translator->translate('filter', 'main'),
					'SORT_COLUMN'          => $datalistSections['sort']['column'],
					'SORT_ORDER'           => $datalistSections['sort']['order'],
					'ELEMENTS_PAGE'        => $datalistSections['page']['current'],
					'ELEMENTS_PER_PAGE'    => $datalistSections['page']['num_elements'],
					'elements_per_page'    => $datalistSections['pagination_dropdown'],
					'form_button' => [
						[
							'ELEMENT_BUTTON_TYPE' => 'submit',
							'ELEMENT_BUTTON_NAME' => 'submit',
						]
					],
					'has_add'                     => $datalistSections['has_add'],
					'LANG_ELEMENTS_PER_PAGE'      => $this->translator->translate('elements_per_page', 'main'),
					'LANG_COUNT_SEARCH_RESULTS'   => sprintf($this->translator->translateWithPlural('count_search_results', $datalistSections['module_name'], $datalistSections['results_count']), $datalistSections['results_count']),
					'elements_pager'              => $datalistSections['pagination_links'],
					'elements_result_header'      => $datalistSections['results_header'],
					'elements_results'            => $datalistSections['results_list']
				]
			]
		];
	}



}