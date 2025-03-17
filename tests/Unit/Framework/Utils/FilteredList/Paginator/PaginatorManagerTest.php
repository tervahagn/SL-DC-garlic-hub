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
use App\Framework\Utils\FilteredList\Paginator\Builder;
use App\Framework\Utils\FilteredList\Paginator\PaginationManager;
use App\Framework\Utils\FilteredList\Paginator\Formatter;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class PaginatorManagerTest extends TestCase
{
	private PaginationManager $paginationManager;
	private Builder $creatorMock;
	private Formatter $rendererMock;
	private BaseFilterParameters $baseFilterMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->creatorMock   = $this->createMock(Builder::class);
		$this->rendererMock  = $this->createMock(Formatter::class);
		$this->baseFilterMock = $this->createMock(BaseFilterParameters::class);

		$this->paginationManager = new PaginationManager($this->creatorMock, $this->rendererMock);
		$this->paginationManager->init($this->baseFilterMock);
	}


	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testSetBaseFilter(): void
	{
		$baseFilterMock = $this->createMock(BaseFilterParameters::class);

		$this->rendererMock->expects($this->once())->method('setBaseFilter');

		$result = $this->paginationManager->init($baseFilterMock);

		$this->assertSame($this->paginationManager, $result);
	}

	#[Group('units')]
	public function testCreatePagination(): void
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
			->method('configure')
			->with($this->baseFilterMock, $totalItems, $usePager, $shortened)
			->willReturnSelf();

		$this->creatorMock->expects($this->once())
			->method('buildPagerLinks')
			->willReturnSelf();

		$this->creatorMock->expects($this->once())
			->method('getPagerLinks')
			->willReturn($pagerLinks);

		$this->paginationManager->createPagination($totalItems, $usePager, $shortened);

		$this->assertTrue(true);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testRenderPagination(): void
	{
		$site = 'hurz';
		$expectedResult = ['some html'];

		$this->rendererMock->expects($this->once())
			->method('setSite')
			->with($site);

		$this->rendererMock->expects($this->once())
			->method('formatLinks')
			->willReturn($expectedResult);

		// just for creating PagerLinks
		$this->paginationManager->createPagination(100);

		$result = $this->paginationManager->formatPaginationLinks($site);

		$this->assertSame($expectedResult, $result);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testRenderElementsPerSiteDropDown(): void
	{
		$this->paginationManager->createDropDown(20, 200, 2);

		$this->rendererMock->expects($this->once())
			->method('formatDropdown')
			->with(['min' => 20, 'max' => 200, 'steps' => 2]);

		$this->paginationManager->formatPaginationDropDown();
	}


}
