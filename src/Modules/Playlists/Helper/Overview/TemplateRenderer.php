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

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class TemplateRenderer
{

	private Translator $translator;
	private readonly Parameters $parameters;


	public function __construct($translator, Parameters $parameters)
	{
		$this->translator = $translator;
		$this->parameters = $parameters;
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function renderTemplate(array $datalistSections): array
	{
		$title = $this->translator->translate('overview', 'playlists');

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
					'element_hidden' =>  $datalistSections['filter_elements']['hidden'],
					'form_element' =>  $datalistSections['filter_elements']['visible'],
					'LANG_ELEMENTS_FILTER' => $this->translator->translate('filter', 'main'),
					'SORT_COLUMN' => $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_SORT_COLUMN),
					'SORT_ORDER' =>  $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_SORT_ORDER),
					'ELEMENTS_PAGE' => $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PAGE),
					'ELEMENTS_PER_PAGE' => $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE),
					'elements_per_page' => $datalistSections['pagination_dropdown'],
					'form_button' => [
						[
							'ELEMENT_BUTTON_TYPE' => 'submit',
							'ELEMENT_BUTTON_NAME' => 'submit',
						]
					],
					'create_playlist_contextmenu' => $this->buildPlaylistContextMenu(),
					'add_allowed' => [
						'ADD_BI_ICON' => 'folder-plus',
						'LANG_ELEMENTS_ADD_LINK' =>	$this->translator->translate('add', 'playlists'),
						'ELEMENTS_ADD_LINK' => '#'

					],
					'LANG_ELEMENTS_PER_PAGE' => $this->translator->translate('elements_per_page', 'main'),
					'LANG_COUNT_SEARCH_RESULTS' => sprintf($this->translator->translateWithPlural('count_search_results', 'playlists', $datalistSections['results_count']), $datalistSections['results_count']),
					'elements_pager' => $datalistSections['pagination_links'],
					'elements_result_header' => $datalistSections['results_header'],
					'elements_results' => $datalistSections['results_body']
				]
			]
		];
	}


	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws InvalidArgumentException
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
}