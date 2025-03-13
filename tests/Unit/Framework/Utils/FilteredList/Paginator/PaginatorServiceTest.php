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


namespace Tests\Unit\Framework\Utils\FilteredList\Paginator;

use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FilteredList\Paginator\Creator;
use App\Framework\Utils\FilteredList\Paginator\PaginatorService;
use App\Framework\Utils\FilteredList\Paginator\Renderer;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class PaginatorServiceTest extends TestCase
{
	private PaginatorService $paginatorService;
	private Creator $creatorMock;
	private Renderer $rendererMock;
	private BaseFilterParameters $baseFilterMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->creatorMock   = $this->createMock(Creator::class);
		$this->rendererMock  = $this->createMock(Renderer::class);
		$this->baseFilterMock = $this->createMock(BaseFilterParameters::class);

		$this->paginatorService = new PaginatorService($this->creatorMock, $this->rendererMock);
		$this->paginatorService->setBaseFilter($this->baseFilterMock);
	}

	#[Group('units')]
	public function testSetBaseFilter(): void
	{
		// Arrange
		$baseFilterMock = $this->createMock(BaseFilterParameters::class);

		// Act
		$result = $this->paginatorService->setBaseFilter($baseFilterMock);

		// Assert
		$this->assertSame($this->paginatorService, $result);
	}

	#[Group('units')]
	public function testCreate(): void
	{
		// Arrange
		$totalItems = 100;
		$usePager = true;
		$shortened = true;
		$pagerLinks = [
			['page' => 1, 'name' => 'Page 1'],
			['page' => 2, 'name' => 'Page 2']
		];

		$this->creatorMock->expects($this->once())
			->method('init')
			->with($this->baseFilterMock, $totalItems, $usePager, $shortened)
			->willReturnSelf();

		$this->creatorMock->expects($this->once())
			->method('buildPagerLinks')
			->willReturnSelf();

		$this->creatorMock->expects($this->once())
			->method('getPagerLinks')
			->willReturn($pagerLinks);

		$this->paginatorService->create($totalItems, $usePager, $shortened);

		$this->assertTrue(true);
	}

	#[Group('units')]
	public function testRenderPagination(): void
	{
		// Arrange
		$site = 'example-site';
		$pagerLinks = [
			['page' => 1, 'name' => 'Page 1'],
			['page' => 2, 'name' => 'Page 2']
		];
		$expectedResult = [
			[
				'ELEMENTS_PAGELINK' => '/example-site?elements_page=1&sort_column=name&sort_order=asc&elements_per_page=10',
				'ELEMENTS_PAGENAME' => 'Page 1',
				'ELEMENTS_PAGENUMBER' => 1
			],
			[
				'ELEMENTS_PAGELINK' => '/example-site?elements_page=2&sort_column=name&sort_order=asc&elements_per_page=10',
				'ELEMENTS_PAGENAME' => 'Page 2',
				'ELEMENTS_PAGENUMBER' => 2
			]
		];

		$this->creatorMock->expects($this->once())
			->method('init')
			->with($this->baseFilterMock, 100)
			->willReturnSelf();

		$this->creatorMock->expects($this->once())
			->method('buildPagerLinks')
			->willReturnSelf();

		$this->creatorMock->expects($this->once())
			->method('getPagerLinks')
			->willReturn($pagerLinks);
		$this->rendererMock->expects($this->once())
			->method('render')
			->with($pagerLinks, $site, $this->baseFilterMock)
			->willReturn($expectedResult);


		$this->paginatorService->create(100);

		$result = $this->paginatorService->renderPagination($site);

		$this->assertSame($expectedResult, $result);
	}

	#[Group('units')]
	public function testRenderElementsPerSiteDropDown(): void
	{
		// Arrange
		$min = 10;
		$max = 30;
		$steps = 10;
		$currentElements = 20;

		$this->baseFilterMock->expects($this->once())
			->method('getValueOfParameter')
			->with(BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE)
			->willReturn((string)$currentElements);

		$expectedResult = [
			[
				'ELEMENTS_PER_PAGE_VALUE' => 10,
				'ELEMENTS_PER_PAGE_NAME' => 10,
				'ELEMENTS_PER_PAGE_SELECTED' => ''
			],
			[
				'ELEMENTS_PER_PAGE_VALUE' => 20,
				'ELEMENTS_PER_PAGE_NAME' => 20,
				'ELEMENTS_PER_PAGE_SELECTED' => 'selected'
			],
			[
				'ELEMENTS_PER_PAGE_VALUE' => 30,
				'ELEMENTS_PER_PAGE_NAME' => 30,
				'ELEMENTS_PER_PAGE_SELECTED' => ''
			]
		];

		// Act
		$result = $this->paginatorService->renderElementsPerSiteDropDown($min, $max, $steps);

		// Assert
		$this->assertSame($expectedResult, $result);
	}

	#[Group('units')]
	public function testRenderElementsPerSiteDropDownWithDefaults(): void
	{
		// Arrange
		$currentElements = 10;

		$this->baseFilterMock->expects($this->once())
			->method('getValueOfParameter')
			->with(BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE)
			->willReturn((string)$currentElements);

		$expectedFirstElements = [
			[
				'ELEMENTS_PER_PAGE_VALUE' => 10,
				'ELEMENTS_PER_PAGE_NAME' => 10,
				'ELEMENTS_PER_PAGE_SELECTED' => 'selected'
			],
			[
				'ELEMENTS_PER_PAGE_VALUE' => 20,
				'ELEMENTS_PER_PAGE_NAME' => 20,
				'ELEMENTS_PER_PAGE_SELECTED' => ''
			]
		];

		// Act
		$result = $this->paginatorService->renderElementsPerSiteDropDown();

		// Assert
		$this->assertCount(10, $result); // 10 bis 100 in 10er Schritten = 10 Elemente
		$this->assertEquals($expectedFirstElements[0], $result[0]);
		$this->assertEquals($expectedFirstElements[1], $result[1]);
	}


}
