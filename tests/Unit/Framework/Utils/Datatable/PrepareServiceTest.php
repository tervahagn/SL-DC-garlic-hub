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


namespace Tests\Unit\Framework\Utils\Datatable;

use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Datatable\Paginator\Preparer;
use App\Framework\Utils\Datatable\PrepareService;
use App\Framework\Utils\Datatable\Results\BodyPreparer;
use App\Framework\Utils\Datatable\Results\HeaderPreparer;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Framework\Utils\Html\FormBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PrepareServiceTest extends TestCase
{
	private PrepareService $prepareService;
	private HeaderPreparer&MockObject $headerPreparerMock;
	private BodyPreparer&MockObject $bodyPreparerMock;
	private Preparer&MockObject $paginationPreparerMock;
	private FormBuilder&MockObject $formBuilderMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->headerPreparerMock = $this->createMock(HeaderPreparer::class);
		$this->bodyPreparerMock = $this->createMock(BodyPreparer::class);
		$this->paginationPreparerMock = $this->createMock(Preparer::class);
		$this->formBuilderMock = $this->createMock(FormBuilder::class);

		$this->prepareService = new PrepareService(
			$this->formBuilderMock,
			$this->paginationPreparerMock,
			$this->headerPreparerMock,
			$this->bodyPreparerMock
		);
	}

	#[Group('units')]
	public function testPrepareFormReturnsPreparedFormArray(): void
	{
		$datatableForm = ['field1' => 'value1', 'field2' => 'value2'];
		$preparedForm = ['preparedField1' => 'preparedValue1', 'preparedField2' => 'preparedValue2'];

		$this->formBuilderMock
			->expects($this->once())
			->method('prepareForm')
			->with($datatableForm)
			->willReturn($preparedForm);

		$result = $this->prepareService->prepareForm($datatableForm);

		$this->assertSame($preparedForm, $result);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testPreparePaginationReturnsPreparedPaginationArray(): void
	{
		$paginationLinks = ['link1' => 'url1', 'link2' => 'url2'];
		$dropDownSettings = ['setting1' => 'value1', 'setting2' => 'value2'];
		$preparedLinks = ['preparedLink1' => 'preparedUrl1'];
		$preparedDropdown = ['preparedDropdown1' => 'value1'];

		$this->paginationPreparerMock
			->expects($this->once())
			->method('prepareLinks')
			->with($paginationLinks)
			->willReturn($preparedLinks);

		$this->paginationPreparerMock
			->expects($this->once())
			->method('prepareDropdown')
			->with($dropDownSettings)
			->willReturn($preparedDropdown);

		$result = $this->prepareService->preparePagination($paginationLinks, $dropDownSettings);

		$this->assertSame(['links' => $preparedLinks, 'dropdown' => $preparedDropdown], $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testPrepareDatatableHeaderReturnsPreparedHeaderArray(): void
	{
		$tableHeaderFields = ['field1', 'field2'];
		$langModules = ['module1', 'module2'];
		$expectedPreparedHeader = ['preparedField1', 'preparedField2'];

		$basefilterParameterMock = $this->createMock(BaseFilterParameters::class);
		$this->prepareService->configure($basefilterParameterMock, 'testModule');
		$this->headerPreparerMock
			->expects($this->once())
			->method('configure')
			->with($basefilterParameterMock, 'testModule', $langModules);

		$this->headerPreparerMock
			->expects($this->once())
			->method('prepareTableHeader')
			->with($tableHeaderFields)
			->willReturn($expectedPreparedHeader);

		$result = $this->prepareService->prepareDatatableHeader($tableHeaderFields, $langModules);

		$this->assertSame($expectedPreparedHeader, $result);
	}

	#[Group('units')]
	public function testGetBodyPreparer(): void
	{
		$result = $this->prepareService->getBodyPreparer();
		$this->assertInstanceOf(BodyPreparer::class, $result);
		$this->assertSame($this->bodyPreparerMock, $result);
	}

}