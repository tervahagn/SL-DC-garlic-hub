<?php

namespace Tests\Framework\Utils\Datatable;

use App\Framework\Utils\Datatable\AbstractDatatableBuilder;
use App\Framework\Utils\Datatable\BuildService;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Framework\Utils\FormParameters\BaseFilterParametersInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ConcreteDatatableBuilder extends AbstractDatatableBuilder
{

	public function buildTitle(): void{}

	public function configureParameters(int $UID): void{}

	public function determineParameters(): void	{}

	public function collectFormElements(): void{}

	public function createTableFields(): static {return $this;}
}

class AbstractDatatableBuilderTest extends TestCase
{
	private AbstractDatatableBuilder $datatableBuilder;
	private BuildService $buildServiceMock;
	private BaseFilterParameters $parametersMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->buildServiceMock = $this->createMock(BuildService::class);
		$this->parametersMock = $this->createMock(BaseFilterParameters::class);

		$this->datatableBuilder = new ConcreteDatatableBuilder($this->buildServiceMock, $this->parametersMock);
	}

	#[Group('units')]
	public function testCreatePaginationWithValidData(): void
	{
		$resultCount = 100;
		$usePager = true;
		$isShortened = true;

		$currentPage = 1;
		$itemsPerPage = 10;
		$paginationLinks = ['page1', 'page2', 'page3'];

		$this->parametersMock
			->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParametersInterface::PARAMETER_ELEMENTS_PAGE, $currentPage],
				[BaseFilterParametersInterface::PARAMETER_ELEMENTS_PER_PAGE, $itemsPerPage],
			]);

		$this->buildServiceMock
			->expects($this->once())
			->method('buildPaginationLinks')
			->with($currentPage, $itemsPerPage, $resultCount, $usePager, $isShortened)
			->willReturn($paginationLinks);

		$this->datatableBuilder->createPagination($resultCount, $usePager, $isShortened);

		$datatableStructure = $this->datatableBuilder->getDatatableStructure();
		$this->assertArrayHasKey('pager', $datatableStructure);
		$this->assertEquals($paginationLinks, $datatableStructure['pager']);
	}

	#[Group('units')]
	public function testCreatePaginationWithoutPager(): void
	{
		$resultCount = 50;
		$usePager = false;
		$isShortened = false;

		$currentPage = 1;
		$itemsPerPage = 20;
		$paginationLinks = ['page1'];

		$this->parametersMock
			->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParametersInterface::PARAMETER_ELEMENTS_PAGE, $currentPage],
				[BaseFilterParametersInterface::PARAMETER_ELEMENTS_PER_PAGE, $itemsPerPage],
			]);

		$this->buildServiceMock
			->expects($this->once())
			->method('buildPaginationLinks')
			->with($currentPage, $itemsPerPage, $resultCount, $usePager, $isShortened)
			->willReturn($paginationLinks);

		$this->datatableBuilder->createPagination($resultCount, $usePager, $isShortened);

		$datatableStructure = $this->datatableBuilder->getDatatableStructure();
		$this->assertArrayHasKey('pager', $datatableStructure);
		$this->assertEquals($paginationLinks, $datatableStructure['pager']);
	}

	#[Group('units')]
	public function testCreatePaginationWithZeroResults(): void
	{
		$resultCount = 0;
		$usePager = true;
		$isShortened = true;

		$currentPage = 1;
		$itemsPerPage = 10;
		$paginationLinks = [];

		$this->parametersMock
			->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParametersInterface::PARAMETER_ELEMENTS_PAGE, $currentPage],
				[BaseFilterParametersInterface::PARAMETER_ELEMENTS_PER_PAGE, $itemsPerPage],
			]);

		$this->buildServiceMock
			->expects($this->once())
			->method('buildPaginationLinks')
			->with($currentPage, $itemsPerPage, $resultCount, $usePager, $isShortened)
			->willReturn($paginationLinks);

		$this->datatableBuilder->createPagination($resultCount, $usePager, $isShortened);

		$datatableStructure = $this->datatableBuilder->getDatatableStructure();
		$this->assertArrayHasKey('pager', $datatableStructure);
		$this->assertEquals($paginationLinks, $datatableStructure['pager']);
	}

	#[Group('units')]
	public function testCreateDropDownWithValidData(): void
	{
		$min = 10;
		$max = 50;
		$steps = 10;

		$dropdownOptions = [10, 20, 30, 40, 50];

		$this->buildServiceMock
			->expects($this->once())
			->method('buildPaginationDropDown')
			->with($min, $max, $steps)
			->willReturn($dropdownOptions);

		$this->datatableBuilder->createDropDown($min, $max, $steps);

		$datatableStructure = $this->datatableBuilder->getDatatableStructure();
		$this->assertArrayHasKey('dropdown', $datatableStructure);
		$this->assertEquals($dropdownOptions, $datatableStructure['dropdown']);
	}

	#[Group('units')]
	public function testCreateDropDownWithCustomSteps(): void
	{
		$min = 10;
		$max = 100;
		$steps = 20;

		$dropdownOptions = [10, 30, 50, 70, 90];

		$this->buildServiceMock
			->expects($this->once())
			->method('buildPaginationDropDown')
			->with($min, $max, $steps)
			->willReturn($dropdownOptions);

		$this->datatableBuilder->createDropDown($min, $max, $steps);

		$datatableStructure = $this->datatableBuilder->getDatatableStructure();
		$this->assertArrayHasKey('dropdown', $datatableStructure);
		$this->assertEquals($dropdownOptions, $datatableStructure['dropdown']);
	}

	#[Group('units')]
	public function testCreateDropDownWithEmptyRange(): void
	{
		$min = 0;
		$max = 0;
		$steps = 1;

		$dropdownOptions = [];

		$this->buildServiceMock
			->expects($this->once())
			->method('buildPaginationDropDown')
			->with($min, $max, $steps)
			->willReturn($dropdownOptions);

		$this->datatableBuilder->createDropDown($min, $max, $steps);

		$datatableStructure = $this->datatableBuilder->getDatatableStructure();
		$this->assertArrayHasKey('dropdown', $datatableStructure);
		$this->assertEquals($dropdownOptions, $datatableStructure['dropdown']);
	}

}