<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);

namespace App\Framework\Utils\Html;

class CheckboxRenderer extends AbstractInputFieldRenderer implements FieldRenderInterface
{
	public function render(CheckboxField|FieldInterface $field): string
	{
		$this->field = $field;
		if (!($this->field instanceof CheckboxField))
			return '';

		$id = $this->field->getId();
		$checked = $this->field->getValue(); // is empty or has cheked

		return '<input type="checkbox" id="'.$id.'" name="'.$id.'" '.$checked.' aria-describedby="error_'.$id.'">';

	}
}