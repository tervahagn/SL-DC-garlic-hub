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

namespace App\Framework\Utils\FilteredList\Results;

class BodyRenderer
{

	public function renderLink(string $valueName, string $title, string $href, string $valueId, string $cssClass = ''): array
	{
		return [
			'CONTROL_ELEMENT_VALUE_NAME'  => $valueName,
			'CONTROL_ELEMENT_VALUE_TITLE' => $title,
			'CONTROL_ELEMENT_VALUE_LINK' => $href,
			'CONTROL_ELEMENT_VALUE_ID' => $valueId,
			'CONTROL_ELEMENT_VALUE_CLASS' => $cssClass
		];
	}

	public function renderUID(int $UID, string $username): array
	{
		return [
			'OWNER_UID'  => $UID,
			'OWNER_NAME' => $username,
		];
	}

	public function renderText(string $text): array
	{
		return [
			'CONTROL_ELEMENT_VALUE_TEXT' => $text
		];
	}
	public function renderAction(string $lang, string $link, string $name, string $cssClass): array
	{
		return 	[
				'LANG_ACTION'       => $lang,
				'LINK_ACTION'       => $link,
				'ACTION_NAME'       => $name,
				'ACTION_ICON_CLASS' => $cssClass
			];
	}

	public function renderActionDelete(string $lang, string $langConfirm, string $link, string $id, string $cssClass): array
	{
		return 	[
			'LANG_DELETE_ACTION'   => $lang,
			'LINK_DELETE_ACTION'   => $link,
			'DELETE_ID'            => $id,
			'LANG_CONFIRM_DELETE'  => $langConfirm,
			'ELEMENT_DELETE_CLASS' => $cssClass
		];
	}

}