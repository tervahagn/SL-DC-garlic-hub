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

namespace App\Framework\Utils\DataGrid;

use App\Framework\Utils\DataGrid\Paginator\Formatter;
use App\Framework\Utils\DataGrid\Results\BodyDataFormatter;
use App\Framework\Utils\DataGrid\Results\HeaderDataFormatter;
use App\Framework\Utils\Html\FormBuilder;

class FormatterServiceLocator
{
	private HeaderDataFormatter $headerFormatter;
	private BodyDataFormatter $bodyFormatter;
	private Formatter $paginationFormatter;
	private FormBuilder $formBuilder;

	/**
	 * @param HeaderDataFormatter $headerFormatter
	 * @param BodyDataFormatter $bodyFormatter
	 * @param Formatter $paginationFormatter
	 * @param FormBuilder $formBuilder
	 */
	public function __construct(FormBuilder $formBuilder,  Formatter $paginationFormatter, HeaderDataFormatter $headerFormatter, BodyDataFormatter $bodyFormatter)
	{
		$this->formBuilder         = $formBuilder;
		$this->paginationFormatter = $paginationFormatter;
		$this->headerFormatter     = $headerFormatter;
		$this->bodyFormatter       = $bodyFormatter;
	}

	public function getFormBuilder(): FormBuilder
	{
		return $this->formBuilder;
	}

	public function getPaginationFormatter(): Formatter
	{
		return $this->paginationFormatter;
	}

	public function getHeaderFormatter(): HeaderDataFormatter
	{
		return $this->headerFormatter;
	}

	public function getBodyFormatter(): BodyDataFormatter
	{
		return $this->bodyFormatter;
	}
}