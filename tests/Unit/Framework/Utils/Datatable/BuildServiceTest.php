<?php

namespace Tests\Unit\Framework\Utils\Datatable;

use App\Framework\Exceptions\FrameworkException;
use App\Framework\Utils\Datatable\BuildService;
use App\Framework\Utils\Datatable\Paginator\Builder;
use App\Framework\Utils\Datatable\Results\HeaderField;
use App\Framework\Utils\Html\FieldInterface;
use App\Framework\Utils\Html\FormBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class BuildServiceTest
 *
 * Unit tests for the BuildService class.
 * This class tests the buildFormField method which creates a form field using the FormBuilder.
 */
class BuildServiceTest extends TestCase
{
	private BuildService $buildService;
	private Builder&MockObject $paginatorBuilderMock;
	private \App\Framework\Utils\Datatable\Results\Builder&MockObject $resultsBuilderMock;
	private FormBuilder&MockObject $formBuilderMock;
	private FieldInterface&MockObject $fieldMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->formBuilderMock = $this->createMock(FormBuilder::class);
		$this->fieldMock = $this->createMock(FieldInterface::class);

		$this->paginatorBuilderMock = $this->createMock(Builder::class);
		$this->resultsBuilderMock   = $this->createMock(\App\Framework\Utils\Datatable\Results\Builder::class);

		$this->buildService = new BuildService($this->formBuilderMock, $this->paginatorBuilderMock, $this->resultsBuilderMock);

	}

	/**
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testBuildFormFieldReturnsFieldInterface(): void
	{
		$attributes = [
			'name' => 'email',
			'type' => 'text',
			'label' => 'Email'
		];

		$this->formBuilderMock
			->expects($this->once())
			->method('createField')
			->with($attributes)
			->willReturn($this->fieldMock);

		$result = $this->buildService->buildFormField($attributes);

		$this->assertSame($this->fieldMock, $result);
	}

	/**
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testBuildFormFieldWithEmptyAttributes(): void
	{
		$this->formBuilderMock
			->expects($this->once())
			->method('createField')
			->with([])
			->willReturn($this->fieldMock);

		$result = $this->buildService->buildFormField();

		$this->assertSame($this->resultsBuilderMock, $this->buildService->getResultsBuilder());
		$this->assertSame($this->fieldMock, $result);
	}

	#[Group('units')]
	public function testCreateDatatableFieldAddsField(): void
	{
		$fieldName = 'name';

		$this->resultsBuilderMock
			->expects($this->once())
			->method('createField')
			->with($fieldName, true);

		$this->buildService->createDatatableField($fieldName, true);
	}

	#[Group('units')]
	public function testCreateDatatableFieldWithUnSortableField(): void
	{
		$fieldName = 'age';

		$this->resultsBuilderMock
			->expects($this->once())
			->method('createField')
			->with($fieldName, false);

		$this->buildService->createDatatableField($fieldName, false);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testGetDatatableFieldsReturnsExpectedFields(): void
	{
		$expectedFields = [
			$this->createMock(HeaderField::class),
			$this->createMock(HeaderField::class)
		];

		$this->resultsBuilderMock
			->expects($this->once())
			->method('getHeaderFields')
			->willReturn($expectedFields);

		$result = $this->buildService->getDatatableFields();

		$this->assertSame($expectedFields, $result);
	}


	#[Group('units')]
	public function testBuildPaginationDropDownReturnsCorrectSettings(): void
	{
		$min = 5;
		$max = 50;
		$steps = 5;

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('createDropDown')
			->with($min, $max, $steps)
			->willReturnSelf();

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('getDropDownSettings')
			->willReturn(['min' => 15, 'max' => 50, 'steps' => 5]);

		$result = $this->buildService->buildPaginationDropDown($min, $max, $steps);

		$this->assertSame(['min' => 15, 'max' => 50, 'steps' => 5], $result);
	}

	#[Group('units')]
	public function testBuildPaginationDropDownWithDefaultValues(): void
	{
		$this->paginatorBuilderMock
			->expects($this->once())
			->method('createDropDown')
			->with(10, 100, 10)
			->willReturnSelf();

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('getDropDownSettings')
			->willReturn(['min' => 10, 'max' => 100, 'steps' => 10]);

		$result = $this->buildService->buildPaginationDropDown();

		$this->assertSame(['min' => 10, 'max' => 100, 'steps' => 10],$result);
	}

	#[Group('units')]
	public function testBuildPaginationLinksWithValidParameters(): void
	{
		$currentPage = 1;
		$itemsPerPage = 10;
		$totalItems = 100;
		$expectedLinks = [
			['name' => '1', 'page' => 1],
			['name' => '2', 'page' => 2],
			['name' => '3', 'page' => 3]
		];

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('configure')
			->with($currentPage, $itemsPerPage, $totalItems, false, true)
			->willReturnSelf();

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('buildPagerLinks')
			->willReturnSelf();

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('getPagerLinks')
			->willReturn($expectedLinks);

		$result = $this->buildService->buildPaginationLinks($currentPage, $itemsPerPage, $totalItems);

		$this->assertSame($expectedLinks, $result);
	}

	#[Group('units')]
	public function testBuildPaginationLinksWithUsePagerTrue(): void
	{
		$currentPage = 2;
		$itemsPerPage = 20;
		$totalItems = 200;
		$expectedLinks = [
			['name' => '1', 'page' => 1],
			['name' => '2', 'page' => 2],
			['name' => '3', 'page' => 3]
		];

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('configure')
			->with($currentPage, $itemsPerPage, $totalItems, true, true)
			->willReturnSelf();

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('buildPagerLinks')
			->willReturnSelf();

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('getPagerLinks')
			->willReturn($expectedLinks);

		$result = $this->buildService->buildPaginationLinks($currentPage, $itemsPerPage, $totalItems, true);

		$this->assertSame($expectedLinks, $result);
	}

	#[Group('units')]
	public function testBuildPaginationLinksWithShortenedFalse(): void
	{
		$currentPage = 3;
		$itemsPerPage = 5;
		$totalItems = 50;
		$expectedLinks = [
			['name' => '1', 'page' => 1],
			['name' => '2', 'page' => 2],
			['name' => '3', 'page' => 3]
		];

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('configure')
			->with($currentPage, $itemsPerPage, $totalItems, false, false)
			->willReturnSelf();

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('buildPagerLinks')
			->willReturnSelf();

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('getPagerLinks')
			->willReturn($expectedLinks);

		$result = $this->buildService->buildPaginationLinks($currentPage, $itemsPerPage, $totalItems, false, false);

		$this->assertSame($expectedLinks, $result);
	}

	#[Group('units')]
	public function testBuildPaginationLinksWithExceedingCurrentPage(): void
	{
		$currentPage = 15;
		$itemsPerPage = 10;
		$totalItems = 100;
		$expectedLinks = []; // Expected behavior for out-of-range currentPage can vary

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('configure')
			->with($currentPage, $itemsPerPage, $totalItems, false, true)
			->willReturnSelf();

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('buildPagerLinks')
			->willReturnSelf();

		$this->paginatorBuilderMock
			->expects($this->once())
			->method('getPagerLinks')
			->willReturn($expectedLinks);

		$result = $this->buildService->buildPaginationLinks($currentPage, $itemsPerPage, $totalItems);

		$this->assertSame($expectedLinks, $result);
	}


}