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


namespace Tests\Unit\Framework\Services;

use App\Framework\Services\AbstractBaseService;
use App\Framework\Services\SearchFilterParamsTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ConcreteTraitService extends AbstractBaseService
{
	use SearchFilterParamsTrait;

	public function getCurrentFilterParameter()
	{
		return $this->currentFilterParams;
	}
	public function setPublicAllResultData(int $total, array $results): static
	{
		return $this->setAllResultData($total, $results);
	}
}
class SearchFilterParamsTraitTest extends TestCase
{
	private readonly ConcreteTraitService $searchFilterService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$loggerMock = $this->createMock(LoggerInterface::class);
		$this->searchFilterService = new ConcreteTraitService($loggerMock);
	}

	#[Group('units')]
	public function testSetCurrentFilterParams(): void
	{
		$filterParams = ['keyword' => 'test', 'category' => 'example'];
		$this->searchFilterService->setCurrentFilterParams($filterParams);

		$this->assertSame($filterParams, $this->searchFilterService->getCurrentFilterParameter());
	}

	#[Group('units')]
	public function testSetAndGetCurrentTotalResult(): void
	{
		$totalResult = 100;
		$this->searchFilterService->setCurrentTotalResult($totalResult);
		$this->assertEquals($totalResult, $this->searchFilterService->getCurrentTotalResult());
	}

	#[Group('units')]
	public function testSetAndGetCompanyArray(): void
	{
		$companies = [1 => 'Company A', 2 => 'Company B'];
		$this->searchFilterService->setCompanyArray($companies);
		$this->assertEquals($companies, $this->searchFilterService->getCompanyArray());
	}

	#[Group('units')]
	public function testSetAndGetCurrentFilterResults(): void
	{
		$filterResults = [['id' => 1, 'name' => 'Result 1'], ['id' => 2, 'name' => 'Result 2']];
		$this->searchFilterService->setCurrentFilterResults($filterResults);
		$this->assertEquals($filterResults, $this->searchFilterService->getCurrentFilterResults());
	}

	#[Group('units')]
	public function testReturnFilteredDomainsArrayForCheckBoxes(): void
	{
		$this->assertEquals([], $this->searchFilterService->returnFilteredDomainsArrayForCheckBoxes());
	}

	#[Group('units')]
	public function testReturnFilteredCompaniesForDropdowns(): void
	{
		$this->searchFilterService->setAllowedCompanyIds([1, 2]);
		$this->searchFilterService->setCompanyArray([1 => 'Company A', 2 => 'Company B', 3 => 'Company C']);
		$expected = [0 => '-', 1 => 'Company A', 2 => 'Company B'];
		$this->assertEquals($expected, $this->searchFilterService->returnFilteredCompaniesForDropdowns());
	}

	#[Group('units')]
	public function testReturnFilteredDomainsForDropdowns(): void
	{
		$this->searchFilterService->setAllowedCompanyIds([1, 2, 3]);
		$this->searchFilterService->setCompanyArray([1 => 'Company A', 2 => 'Company B', 3 => 'Company C']);
		$expected = [0 => '-', 2 => 'Company A', 4 => 'Company B', 8 => 'Company C'];
		$this->assertEquals($expected, $this->searchFilterService->returnFilteredDomainsForDropdowns());
	}

	#[Group('units')]
	public function testSetAllResultData(): void
	{
		$total = 50;
		$results = [['id' => 1, 'name' => 'Result 1'], ['id' => 2, 'name' => 'Result 2']];
		$this->searchFilterService->setPublicAllResultData($total, $results);
		$this->assertEquals($total, $this->searchFilterService->getCurrentTotalResult());
		$this->assertEquals($results, $this->searchFilterService->getCurrentFilterResults());
	}
}
