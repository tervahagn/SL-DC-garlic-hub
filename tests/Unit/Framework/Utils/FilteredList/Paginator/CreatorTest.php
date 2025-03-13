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

use App\Framework\Utils\FilteredList\Paginator\Creator;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class CreatorTest extends TestCase
{
	private Creator $creator;
	private BaseFilterParameters $baseFilterMock;

	protected function setUp(): void
	{
		$this->baseFilterMock = $this->createMock(BaseFilterParameters::class);
		$this->creator = new Creator();
	}

	#[Group('units')]
	public function testInitSetsPropertiesCorrectly(): void
	{
		$this->baseFilterMock->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParameters::PARAMETER_ELEMENTS_PAGE, 2],
				[BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE, 10],
			]);

		$this->creator->init($this->baseFilterMock, 50, true, false);
		$this->creator->buildPagerLinks();

		$pagerLinks = $this->creator->getPagerLinks();
		$this->assertIsArray($pagerLinks);
		$this->assertCount(9, $pagerLinks);

	}

	#[Group('units')]
	public function testInitHandlesMinimumValues(): void
	{
		$this->baseFilterMock->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParameters::PARAMETER_ELEMENTS_PAGE, 0],
				[BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE, 1],
			]);

		$this->creator->init($this->baseFilterMock, 1);
		$this->creator->buildPagerLinks();

		$expectedLinks = [['name' => 1, 'page' => 1, 'active' => 1]];
		$pagerLinks    = $this->creator->getPagerLinks();
		$this->assertEquals($expectedLinks, $pagerLinks);
	}

	#[Group('units')]
	public function testBuildPagerLinksWithPagerOnFirstPage(): void
	{
		$this->baseFilterMock->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParameters::PARAMETER_ELEMENTS_PAGE, 1],
				[BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE, 10],
			]);

		$this->creator->init($this->baseFilterMock, 50, true);
		$this->creator->buildPagerLinks();

		$expectedLinks = [
			['name' => '1', 'page' => 1, 'active' => true],
			['name' => '2', 'page' => 2, 'active' => false],
			['name' => '3', 'page' => 3, 'active' => false],
			['name' => '4', 'page' => 4, 'active' => false],
			['name' => '5', 'page' => 5, 'active' => false],
			['name' => '›', 'page' => 2],
			['name' => '»', 'page' => 5]
		];

		$this->assertSame($expectedLinks, $this->creator->getPagerLinks());
	}

	#[Group('units')]
	public function testBuildPagerLinksWithPagerOnMiddlePage(): void
	{
		$this->baseFilterMock->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParameters::PARAMETER_ELEMENTS_PAGE, 3],
				[BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE, 10],
			]);

		$this->creator->init($this->baseFilterMock, 50, true);
		$this->creator->buildPagerLinks();

		$expectedLinks = [
			['name' => '«', 'page' => 1],
			['name' => '‹', 'page' => 2],
			['name' => '1', 'page' => 1, 'active' => false],
			['name' => '2', 'page' => 2, 'active' => false],
			['name' => '3', 'page' => 3, 'active' => true],
			['name' => '4', 'page' => 4, 'active' => false],
			['name' => '5', 'page' => 5, 'active' => false],
			['name' => '›', 'page' => 4],
			['name' => '»', 'page' => 5]
		];

		$this->assertSame($expectedLinks, $this->creator->getPagerLinks());
	}

	#[Group('units')]
	public function testBuildPagerLinksWithPagerOnLastPage(): void
	{
		$this->baseFilterMock->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParameters::PARAMETER_ELEMENTS_PAGE, 5],
				[BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE, 10],
			]);

		$this->creator->init($this->baseFilterMock, 50, true);
		$this->creator->buildPagerLinks();

		$expectedLinks = [
			['name' => '«', 'page' => 1],
			['name' => '‹', 'page' => 4],
			['name' => '1', 'page' => 1, 'active' => false],
			['name' => '2', 'page' => 2, 'active' => false],
			['name' => '3', 'page' => 3, 'active' => false],
			['name' => '4', 'page' => 4, 'active' => false],
			['name' => '5', 'page' => 5, 'active' => true]
		];

		$this->assertSame($expectedLinks, $this->creator->getPagerLinks());
	}

	#[Group('unsits')]
	public function testBuildPagerLinksWithPagerAndEmptyList(): void
	{
		$this->baseFilterMock->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParameters::PARAMETER_ELEMENTS_PAGE, 1],
				[BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE, 10],
			]);

		$this->creator->init($this->baseFilterMock, 0, true);
		$this->creator->buildPagerLinks();

		$expectedLinks = [
			['name' => '1', 'page' => 1, 'active' => true],
		];

		$this->assertSame($expectedLinks, $this->creator->getPagerLinks());
	}

	#[Group('units')]
	public function testBuildPagerLinksLongAndShortenFalse(): void
	{
		$this->baseFilterMock->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParameters::PARAMETER_ELEMENTS_PAGE, 2],
				[BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE, 10],
			]);

		$this->creator->init($this->baseFilterMock, 100, true, false);
		$this->creator->buildPagerLinks();

		$expectedLinks = [
			['name' => '«', 'page' => 1],
			['name' => '‹', 'page' => 1],
			['name' => '1', 'page' => 1, 'active' => false],
			['name' => '2', 'page' => 2, 'active' => true],
			['name' => '3', 'page' => 3, 'active' => false],
			['name' => '4', 'page' => 4, 'active' => false],
			['name' => '5', 'page' => 5, 'active' => false],
			['name' => '6', 'page' => 6, 'active' => false],
			['name' => '7', 'page' => 7, 'active' => false],
			['name' => '8', 'page' => 8, 'active' => false],
			['name' => '9', 'page' => 9, 'active' => false],
			['name' => '10', 'page' => 10, 'active' => false],
			['name' => '›', 'page' => 3],
			['name' => '»', 'page' => 10],
		];

		$this->assertSame($expectedLinks, $this->creator->getPagerLinks());
	}
	#[Group('units')]

	public function testBuildPagerLinksLong(): void
	{
		$this->baseFilterMock->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParameters::PARAMETER_ELEMENTS_PAGE, 2],
				[BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE, 10],
			]);

		$this->creator->init($this->baseFilterMock, 100, true);
		$this->creator->buildPagerLinks();

		$expectedLinks = [
			['name' => '«', 'page' => 1],
			['name' => '‹', 'page' => 1],
			['name' => '1', 'page' => 1, 'active' => false],
			['name' => '2', 'page' => 2, 'active' => true],
			['name' => '3', 'page' => 3, 'active' => false],
			['name' => '4', 'page' => 4, 'active' => false],
			['name' => '8', 'page' => 8, 'active' => false],
			['name' => '9', 'page' => 9, 'active' => false],
			['name' => '10', 'page' => 10, 'active' => false],
			['name' => '›', 'page' => 3],
			['name' => '»', 'page' => 10],
		];

		$this->assertSame($expectedLinks, $this->creator->getPagerLinks());
	}
}
