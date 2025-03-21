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

use App\Framework\Utils\Datatable\UrlBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class UrlBuilderTest extends TestCase
{
	#[Group('units')]
	public function testBuildFilterUrlWithoutAdditionalParameters(): void
	{
		$urlBuilder = (new UrlBuilder())
			->setSite('https://example.com')
			->setPage(1)
			->setElementsPerPage(10)
			->setSortColumn('name')
			->setSortOrder('asc');

		$expectedUrl = 'https://example.com?elements_page=1&sort_column=name&sort_order=asc&elements_per_page=10';

		$this->assertEquals($expectedUrl, $urlBuilder->buildFilterUrl());
	}

	#[Group('units')]
	public function testClearAdditionalUrlParameters(): void
	{
		$urlBuilder = (new UrlBuilder())
			->setSite('https://example.com')
			->setPage(1)
			->setElementsPerPage(10)
			->setSortColumn('name')
			->setSortOrder('asc')
			->addAdditionalUrlParameter('key1', 'value1')
			->addAdditionalUrlParameter('key2', 'value2')
			->addAdditionalUrlParameter('key3', 'value3');

		$this->assertTrue($urlBuilder->hasAdditionalUrlParameters());

		$urlBuilder->clearAdditionalUrlParameters();

		$this->assertFalse($urlBuilder->hasAdditionalUrlParameters());

		$expectedUrl = 'https://example.com?elements_page=1&sort_column=name&sort_order=asc&elements_per_page=10';

		$this->assertEquals($expectedUrl, $urlBuilder->buildFilterUrl());
	}

	#[Group('units')]
	public function testBuildFilterUrlWithAdditionalParameters(): void
	{
		$urlBuilder = (new UrlBuilder())
			->setSite('https://example.com')
			->setPage(2)
			->setElementsPerPage(20)
			->setSortColumn('date')
			->setSortOrder('desc')
			->addAdditionalUrlParameter('filter', 'active')
			->addAdditionalUrlParameter('category', 'news');

		$expectedUrl = 'https://example.com?elements_page=2&sort_column=date&sort_order=desc&elements_per_page=20&filter=active&category=news';

		$this->assertEquals($expectedUrl, $urlBuilder->buildFilterUrl());
	}

}
