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

use App\Framework\Utils\Datatable\Paginator\Builder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
	private Builder $builder;

	protected function setUp(): void
	{
		$this->builder = new Builder();
	}

	#[Group('units')]
	public function testInitSetsPropertiesCorrectly(): void
	{

		$this->builder->configure(2, 10, 50, true, false);
		$this->builder->buildPagerLinks();

		$pagerLinks = $this->builder->getPagerLinks();
		$this->assertIsArray($pagerLinks);
		$this->assertCount(9, $pagerLinks);

	}

	#[Group('units')]
	public function testInitHandlesMinimumValues(): void
	{
		$this->builder->configure(0,1, 1, true, true);
		$this->builder->buildPagerLinks();

		$expectedLinks = [['name' => 1, 'page' => 1, 'active' => 1]];
		$pagerLinks    = $this->builder->getPagerLinks();
		$this->assertEquals($expectedLinks, $pagerLinks);
	}

	#[Group('units')]
	public function testBuildPagerLinksWithPagerOnFirstPage(): void
	{

		$this->builder->configure(1, 10, 50, true, true);
		$this->builder->buildPagerLinks();

		$expectedLinks = [
			['name' => '1', 'page' => 1, 'active' => true],
			['name' => '2', 'page' => 2, 'active' => false],
			['name' => '3', 'page' => 3, 'active' => false],
			['name' => '4', 'page' => 4, 'active' => false],
			['name' => '5', 'page' => 5, 'active' => false],
			['name' => '›', 'page' => 2],
			['name' => '»', 'page' => 5]
		];

		$this->assertSame($expectedLinks, $this->builder->getPagerLinks());
	}

	#[Group('units')]
	public function testBuildPagerLinksWithPagerOnMiddlePage(): void
	{
		$this->builder->configure(3, 10, 50, true, false);
		$this->builder->buildPagerLinks();

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

		$this->assertSame($expectedLinks, $this->builder->getPagerLinks());
	}

	#[Group('units')]
	public function testBuildPagerLinksWithPagerOnLastPage(): void
	{
		$this->builder->configure(5, 10, 50, true, true);
		$this->builder->buildPagerLinks();

		$expectedLinks = [
			['name' => '«', 'page' => 1],
			['name' => '‹', 'page' => 4],
			['name' => '1', 'page' => 1, 'active' => false],
			['name' => '2', 'page' => 2, 'active' => false],
			['name' => '3', 'page' => 3, 'active' => false],
			['name' => '4', 'page' => 4, 'active' => false],
			['name' => '5', 'page' => 5, 'active' => true]
		];

		$this->assertSame($expectedLinks, $this->builder->getPagerLinks());
	}

	#[Group('units')]
	public function testBuildPagerLinksWithPagerAndEmptyList(): void
	{

		$this->builder->configure(1,10, 0, true, true);
		$this->builder->buildPagerLinks();

		$expectedLinks = [
			['name' => '1', 'page' => 1, 'active' => true],
		];

		$this->assertSame($expectedLinks, $this->builder->getPagerLinks());
	}

	#[Group('units')]
	public function testBuildPagerLinksLongAndShortenFalse(): void
	{
		$this->builder->configure(2, 10, 100, true, false);
		$this->builder->buildPagerLinks();

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

		$this->assertSame($expectedLinks, $this->builder->getPagerLinks());
	}

	#[Group('units')]
	public function testBuildPagerLinksLong(): void
	{
		$this->builder->configure(2, 10, 100, true, true);
		$this->builder->buildPagerLinks();

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

		$this->assertSame($expectedLinks, $this->builder->getPagerLinks());
	}

	#[Group('units')]
	public function testcreateDropdown(): void
	{
		$this->builder->createDropDown(10, 1000, 100);

		$expected =  ['min' => 10, 'max' => 1000, 'steps' => 100];
		$this->assertSame($expected, $this->builder->getDropDownSettings());

	}


}
