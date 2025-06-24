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

		return '<input type="text" id="'.$id.'" class="verification-link" title="'.$this->field->getTitle().'" value="'.$this->field->getValue().'" readonly>
<button type="button" data-id="'.$id.'" class="copy-verification-link" title="'.$this->field->getTitle().'."><i class="bi bi-clipboard"></i></button>
<button type="button" data-id="'.$id.'" class="delete-verification-link" title="'.$this->field->getDeleteTitle().'."><i class="bi bi-trash"></i></button>
<button type="button" data-id="'.$id.'" class="refresh-verification-link" title="'.$this->field->getRefreshTitle().'."><i class="bi bi-arrow-clockwise"></i></button>
';
	}
}