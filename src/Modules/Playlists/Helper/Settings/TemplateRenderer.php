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

namespace App\Modules\Playlists\Helper\Settings;

use App\Framework\Core\Translate\Translator;

class TemplateRenderer
{
	private Translator $translator;

	public function __construct(Translator $translator)
	{
		$this->translator = $translator;
	}

	public function renderTemplate(array $elements, string $playlistMode): array
	{
		$title = $this->translator->translate('settings', 'playlists'). ' - ' .
			$this->translator->translateArrayForOptions('playlist_mode_selects', 'playlists')[strtolower($playlistMode)];

		return [
			'main_layout' => [
				'LANG_PAGE_TITLE' => $title,
				'additional_css' => ['/css/playlists/settings.css'],
				'footer_modules' => ['/js/playlists/settings/init.js']
			],
			'this_layout' => [
				'template' => 'playlists/edit', // Template-name
				'data' => [
					'LANG_PAGE_HEADER' => $title,
					'FORM_ACTION' => '/playlists/settings',
					'element_hidden' => $elements['hidden'],
					'form_element' => $elements['visible'],
					'form_button' => [
						[
							'ELEMENT_BUTTON_TYPE' => 'submit',
							'ELEMENT_BUTTON_NAME' => 'submit',
							'LANG_ELEMENT_BUTTON' => $this->translator->translate('save', 'main')
						]
					]
				]
			]
		];
	}
}