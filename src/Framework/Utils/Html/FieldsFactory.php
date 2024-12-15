<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

namespace App\Framework\Utils\Html;

class FieldsFactory
{
	public function createTextField($elementId, $elementName = null, $defaultValue = null): TextField
	{
		return new TextField(
					$elementId,
					$elementName,
					$defaultValue
				);
	}

	public function createEmailField($elementId, $elementName = null, $defaultValue = null): EmailField
	{
		return new EmailField(
			$elementId,
			$elementName,
			$defaultValue
		);
	}

	public function createPasswordField($elementId, $elementName = null, $defaultValue = null): PasswordField
	{
		return new PasswordField(
			$elementId,
			$elementName,
			$defaultValue
		);
	}

	public function createCsrfTokenField($elementId, $elementName = null, $defaultValue = null): CsrfTokenField
	{
		return new CsrfTokenField(
			$elementId,
			$elementName,
			$defaultValue
		);
	}

}