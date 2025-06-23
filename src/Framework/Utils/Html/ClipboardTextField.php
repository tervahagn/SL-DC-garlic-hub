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

class ClipboardTextField extends AbstractInputField
{
	private string $removeTitle = '';
	private string $refreshTitle = '';

	public function setRemoveTitle(string $removeTitle): static
	{
		$this->removeTitle = $removeTitle;
		return $this;
	}

	public function getRemoveTitle(): string
	{
		return $this->removeTitle;
	}

	public function setRefreshTitle(string $refreshTitle): static
	{
		$this->refreshTitle = $refreshTitle;
		return $this;
	}

	public function getRefreshTitle(): string
	{
		return $this->refreshTitle;
	}
}