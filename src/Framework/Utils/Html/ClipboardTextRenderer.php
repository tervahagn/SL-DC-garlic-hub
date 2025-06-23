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

class ClipboardTextRenderer extends AbstractInputFieldRenderer implements FieldRenderInterface
{
	public function render(ClipboardTextField|FieldInterface $field): string
	{
		$this->field = $field;
		$id = $this->field->getId();

		return '<input type="text" id="'.$id.'" aria-describedby="error_'.$id.'" title="'.$this->field->getTitle().'">
<button class="btn" type="button" id="btn_'.$id.'" title="'.$this->field->getButtonTitle().'.">
    <i class="bi bi-clipboard"></i>
  </button>		';
	}
}