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

use App\Framework\Utils\FilteredList\Paginator\PaginationManager;

class ResultsServiceLocator
{
	private Creator $creator;
	private HeaderRenderer $headerRenderer;
	private BodyRenderer $bodyRenderer;
	private PaginationManager $paginationManager;

	public function __construct(Creator $creator, HeaderRenderer $headerRenderer, BodyRenderer $bodyRenderer, PaginationManager $paginationManager)
	{
		$this->creator = $creator;
		$this->headerRenderer = $headerRenderer;
		$this->bodyRenderer = $bodyRenderer;
		$this->paginationManager = $paginationManager;
	}

	public function getCreator(): Creator
	{
		return $this->creator;
	}

	public function getHeaderRenderer(): HeaderRenderer
	{
		return $this->headerRenderer;
	}

	public function getBodyRenderer(): BodyRenderer
	{
		return $this->bodyRenderer;
	}

	public function getPaginationManager(): PaginationManager
	{
		return $this->paginationManager;
	}

}