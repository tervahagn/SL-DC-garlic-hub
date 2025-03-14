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

use App\Framework\Utils\FormParameters\BaseFilterParameters;

class ResultsServiceLocator
{
	private Renderer $renderer;
	private TranslatorManager $translatorManager;
	private HeaderFieldFactory $headerFieldFactory;
	private string $site;
	private BaseFilterParameters $filterParameters;
	public function __construct(Renderer $renderer, TranslatorManager $translatorManager, HeaderFieldFactory $headerFieldFactory)
	{
		$this->renderer = $renderer;
		$this->translatorManager = $translatorManager;
		$this->headerFieldFactory = $headerFieldFactory;
	}

	public function init(BaseFilterParameters $filterParameter, string $site): void
	{
		$this->renderer->init($filterParameter, $site);
	}

	public function createHeaderField(): HeaderField
	{
		return $this->headerFieldFactory->create();
	}

	public function getRenderer(): Renderer
	{
		return $this->renderer;
	}

	public function addLanguageModule(string $moduleName): TranslatorManager
	{
		return $this->translatorManager->addLanguageModule($moduleName);
	}

}