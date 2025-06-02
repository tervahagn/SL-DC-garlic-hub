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

namespace App\Framework\Utils\Datatable\Results;

class BodyPreparer
{

	public function formatSpan(string $valueName, string $title,  string $valueId, string $cssClass = ''): array
	{
		return [
			'CONTROL_ELEMENT_VALUE_NAME'  => $valueName,
			'CONTROL_ELEMENT_VALUE_TITLE' => $title,
			'CONTROL_ELEMENT_VALUE_ID' => $valueId,
			'CONTROL_ELEMENT_VALUE_CLASS' => $cssClass,
		];
	}

	public function formatLink(string $valueName, string $title, string $href, string $valueId, string $cssClass = '', string $addText = ''): array
	{
		return [
			'CONTROL_ELEMENT_VALUE_NAME'  => $valueName,
			'CONTROL_ELEMENT_VALUE_TITLE' => $title,
			'CONTROL_ELEMENT_VALUE_LINK' => $href,
			'CONTROL_ELEMENT_VALUE_ID' => $valueId,
			'CONTROL_ELEMENT_VALUE_CLASS' => $cssClass,
			'CONTROL_ELEMENT_ADDITIONAL_TEXT' => $addText
		];
	}

	public function formatUID(int $UID, string $username): array
	{
		return [
			'OWNER_UID'  => $UID,
			'OWNER_NAME' => $username,
		];
	}

	public function formatText(string $text): array
	{
		return [
			'CONTROL_ELEMENT_VALUE_TEXT' => $text
		];
	}

	public function formatIcon(string $iconClass, string $title): array
	{
		return [
			'ICON_CLASS' => $iconClass,
			'ICON_TITLE' => $title
		];
	}

	public function formatAction(string $lang, string $link, string $name, string $id, string $cssClass): array
	{
		return 	[
				'LANG_ACTION'       => $lang,
				'LINK_ACTION'       => $link,
				'ACTION_ID'         => $id,
				'ACTION_NAME'       => $name,
				'ACTION_ICON_CLASS' => $cssClass
			];
	}

	public function formatActionDelete(string $lang, string $langConfirm, string $id, string $cssClass): array
	{
		return 	[
			'LANG_DELETE_ACTION'   => $lang,
			'DELETE_ID'            => $id,
			'LANG_CONFIRM_DELETE'  => $langConfirm,
			'ELEMENT_DELETE_CLASS' => $cssClass
		];
	}

}