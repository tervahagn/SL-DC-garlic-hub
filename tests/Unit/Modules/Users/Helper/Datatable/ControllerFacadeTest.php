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


namespace Tests\Unit\Modules\Users\Helper\Datatable;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Modules\Users\Helper\Datatable\ControllerFacade;
use App\Modules\Users\Helper\Datatable\DatatableBuilder;
use App\Modules\Users\Helper\Datatable\DatatablePreparer;
use App\Modules\Users\Services\UsersDatatableService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ControllerFacadeTest extends TestCase
{
	private ControllerFacade $controllerFacade;
	private MockObject $mockDatatableBuilder;
	private MockObject $mockDatatableFormatter;
	private MockObject $mockUsersService;
	private MockObject $mockTranslator;
	private MockObject $mockSession;

	protected function setUp(): void
	{
		$this->mockDatatableBuilder = $this->createMock(DatatableBuilder::class);
		$this->mockDatatableFormatter = $this->createMock(DatatablePreparer::class);
		$this->mockUsersService = $this->createMock(UsersDatatableService::class);
		$this->mockTranslator = $this->createMock(Translator::class);
		$this->mockSession = $this->createMock(Session::class);

		$this->controllerFacade = new ControllerFacade(
			$this->mockDatatableBuilder,
			$this->mockDatatableFormatter,
			$this->mockUsersService
		);

		$this->mockUsersService->method('getCurrentTotalResult')->willReturn(42);
	}


	#[Group('units')]
	public function testConfigure(): void
	{
		$mockUID = 12345;
		$mockUserData = ['UID' => $mockUID];

		$this->mockSession->expects($this->once())
			->method('get')
			->with('user')
			->willReturn($mockUserData);

		$this->mockUsersService->expects($this->once())
			->method('setUID')
			->with($mockUID);

		$this->mockDatatableBuilder->expects($this->once())
			->method('configureParameters')
			->with($mockUID);

		$this->mockDatatableBuilder->expects($this->once())
			->method('setTranslator')
			->with($this->mockTranslator);

		$this->mockDatatableFormatter->expects($this->once())
			->method('setTranslator')
			->with($this->mockTranslator);

		$this->controllerFacade->configure($this->mockTranslator, $this->mockSession);

	}

	#[Group('units')]
	public function testProcessSubmittedUserInput(): void
	{
		// Arrange
		$this->mockDatatableBuilder->expects($this->once())->method('determineParameters');

		$this->mockUsersService->expects($this->once())->method('loadUsersForOverview');

		$this->controllerFacade->processSubmittedUserInput();
	}

	#[Group('units')]
	public function testPrepareDataGrid(): void
	{
		// Arrange
		$this->mockDatatableBuilder->expects($this->once())->method('buildTitle');
		$this->mockDatatableBuilder->expects($this->once())->method('collectFormElements');
		$this->mockDatatableBuilder->expects($this->once())
			->method('createPagination')
			->with(42);
		$this->mockDatatableBuilder->expects($this->once())->method('createDropDown');
		$this->mockDatatableBuilder->expects($this->once())->method('createTableFields');

		// Act
		$result = $this->controllerFacade->prepareDataGrid();

		// Assert
		$this->assertSame($this->controllerFacade, $result);
	}


	#[Group('units')]
	public function testPrepareUITemplate(): void
	{
		$mockUID = 12345;
		$mockUserData = ['UID' => $mockUID];

		$this->mockSession->expects($this->once())->method('get')
			->with('user')
			->willReturn($mockUserData);
		$this->controllerFacade->configure($this->mockTranslator, $this->mockSession);

		$mockDatatableStructure = [
			'pager' => ['page_1', 'page_2'],
			'dropdown' => ['option_1', 'option_2'],
			'form' => ['field_1' => 'value_1'],
			'header' => ['header_1', 'header_2'],
			'title' => 'Mock Title'
		];

		$mockPagination = [
			'dropdown' => 'mock_dropdown',
			'links' => 'mock_links',
		];

		$mockFormattedList = ['row_1', 'row_2'];
		$currentTotalResult = 42;

		$this->mockDatatableBuilder->expects($this->once())
			->method('getDatatableStructure')
			->willReturn($mockDatatableStructure);

		$this->mockDatatableFormatter->expects($this->once())
			->method('preparePagination')
			->with($mockDatatableStructure['pager'], $mockDatatableStructure['dropdown'])
			->willReturn($mockPagination);

		$this->mockDatatableFormatter->expects($this->once())
			->method('prepareFilterForm')
			->with($mockDatatableStructure['form'])
			->willReturn(['prepared_filter_form']);

		$this->mockDatatableFormatter->expects($this->once())
			->method('prepareAdd')
			->with('person-add')
			->willReturn(['prepared_add']);

		$this->mockDatatableFormatter->expects($this->once())
			->method('prepareTableHeader')
			->with($mockDatatableStructure['header'], ['users', 'main'])
			->willReturn(['prepared_header']);

		$this->mockDatatableFormatter->expects($this->once())
			->method('prepareSort')
			->willReturn(['prepared_sort']);

		$this->mockDatatableFormatter->expects($this->once())
			->method('preparePage')
			->willReturn(['prepared_page']);

		$this->mockUsersService->expects($this->once())
			->method('getCurrentTotalResult')
			->willReturn($currentTotalResult);

		$this->mockDatatableFormatter->expects($this->once())
			->method('prepareTableBody')
			->with($this->mockUsersService->getCurrentFilterResults(), $mockDatatableStructure['header'], $this->anything())
			->willReturn($mockFormattedList);

		// Act
		$result = $this->controllerFacade->prepareUITemplate();

		// Assert
		$this->assertEquals([
			'filter_elements' => ['prepared_filter_form'],
			'pagination_dropdown' => 'mock_dropdown',
			'pagination_links' => 'mock_links',
			'has_add' => ['prepared_add'],
			'results_header' => ['prepared_header'],
			'results_list' => $mockFormattedList,
			'results_count' => $currentTotalResult,
			'title' => 'Mock Title',
			'template_name' => 'users/datatable',
			'module_name' => 'users',
			'additional_css' => ['/css/users/overview.css'],
			'footer_modules' => ['/js/users/overview/init.js'],
			'sort' => ['prepared_sort'],
			'page' => ['prepared_page']
		], $result);
	}
}
