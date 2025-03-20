<?php

namespace Tests\Unit\Framework\Utils\Datatable\Results;

use App\Framework\Utils\Datatable\Results\HeaderField;
use App\Framework\Utils\Datatable\Results\HeaderPreparer;
use App\Framework\Utils\Datatable\Results\TranslatorManager;
use App\Framework\Utils\Datatable\UrlBuilder;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Framework\Utils\FormParameters\BaseFilterParametersInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class HeaderPreparerTest extends TestCase
{
	private TranslatorManager $translatorManagerMock;
	private UrlBuilder $urlBuilderMock;
	private BaseFilterParameters $filterParametersMock;
	private HeaderPreparer $headerPreparer;

	protected function setUp(): void
	{
		$this->translatorManagerMock = $this->createMock(TranslatorManager::class);
		$this->urlBuilderMock = $this->createMock(UrlBuilder::class);
		$this->filterParametersMock = $this->createMock(BaseFilterParameters::class);

		$this->headerPreparer = new HeaderPreparer($this->translatorManagerMock, $this->urlBuilderMock);
	}

	#[Group('units')]
	public function testConfigureAddsNoModulesWhenEmptyArray(): void
	{
		$site = 'test-site';
		$languageModules = [];

		$this->urlBuilderMock
			->expects($this->once())
			->method('setSite')
			->with($site);

		$this->translatorManagerMock
			->expects($this->never())
			->method('addLanguageModule');

		$this->headerPreparer->configure($this->filterParametersMock, $site, $languageModules);

		$this->assertNotNull($this->headerPreparer);
	}

	#[Group('units')]
	public function testConfigureSetsFilterParameterCorrectly(): void
	{
		$site = 'example-site';
		$languageModules = ['lang1'];

		$this->headerPreparer->configure($this->filterParametersMock, $site, $languageModules);

		$this->assertNotNull($this->headerPreparer);
	}

	#[Group('units')]
	public function testPrepareTableHeaderReturnsCorrectHeaderForSortableField(): void
	{
		$headerFieldMock = $this->createMock(HeaderField::class);
		$headerFieldMock->method('getName')->willReturn('column1');
		$headerFieldMock->method('isSortable')->willReturn(true);
		$this->filterParametersMock
			->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParametersInterface::PARAMETER_SORT_COLUMN, 'column1'],
				[BaseFilterParametersInterface::PARAMETER_SORT_ORDER, 'asc'],
				[BaseFilterParametersInterface::PARAMETER_ELEMENTS_PAGE, 1],
				[BaseFilterParametersInterface::PARAMETER_ELEMENTS_PER_PAGE, 10]
			]);

		$site = 'example-site';
		$languageModules = ['lang1'];

		$this->headerPreparer->configure($this->filterParametersMock, $site, $languageModules);


		$this->urlBuilderMock->expects($this->once())
			->method('buildFilterUrl')
			->willReturn('http://test.com/sort');

		$this->translatorManagerMock->expects($this->once())
			->method('translate')
			->with($headerFieldMock)
			->willReturn('Translated Column 1');

		$expectedResult = [
			[
				'CONTROL_NAME' => 'column1',
				'if_sortable' => [
					'SORTABLE_ORDER' => '▼',
					'SORT_CONTROL_NAME' => 'column1',
					'LINK_CONTROL_SORT_ORDER' => 'http://test.com/sort',
					'LANG_CONTROL_NAME' => 'Translated Column 1',
				],
			],
		];

		$result = $this->headerPreparer->prepareTableHeader([$headerFieldMock]);

		$this->assertEquals($expectedResult, $result);
	}

	#[Group('units')]
	public function testPrepareTableHeaderReturnsCorrectHeaderForSortableFieldDESC(): void
	{
		$headerFieldMock = $this->createMock(HeaderField::class);
		$headerFieldMock->method('getName')->willReturn('column1');
		$headerFieldMock->method('isSortable')->willReturn(true);
		$this->filterParametersMock
			->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParametersInterface::PARAMETER_SORT_COLUMN, 'column1'],
				[BaseFilterParametersInterface::PARAMETER_SORT_ORDER, 'desc'],
				[BaseFilterParametersInterface::PARAMETER_ELEMENTS_PAGE, 1],
				[BaseFilterParametersInterface::PARAMETER_ELEMENTS_PER_PAGE, 10]
			]);

		$site = 'example-site';
		$languageModules = ['lang1'];

		$this->headerPreparer->configure($this->filterParametersMock, $site, $languageModules);


		$this->urlBuilderMock->expects($this->once())
			->method('buildFilterUrl')
			->willReturn('http://test.com/sort');

		$this->translatorManagerMock->expects($this->once())
			->method('translate')
			->with($headerFieldMock)
			->willReturn('Translated Column 1');

		$expectedResult = [
			[
				'CONTROL_NAME' => 'column1',
				'if_sortable' => [
					'SORTABLE_ORDER' => '▲',
					'SORT_CONTROL_NAME' => 'column1',
					'LINK_CONTROL_SORT_ORDER' => 'http://test.com/sort',
					'LANG_CONTROL_NAME' => 'Translated Column 1',
				],
			],
		];

		$result = $this->headerPreparer->prepareTableHeader([$headerFieldMock]);

		$this->assertEquals($expectedResult, $result);
	}

	#[Group('units')]
	public function testPrepareTableHeaderReturnsCorrectHeaderForSortableFieldNotEqual(): void
	{
		$headerFieldMock = $this->createMock(HeaderField::class);
		$headerFieldMock->method('getName')->willReturn('column_alternative');
		$headerFieldMock->method('isSortable')->willReturn(true);
		$this->filterParametersMock
			->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParametersInterface::PARAMETER_SORT_COLUMN, 'column1'],
				[BaseFilterParametersInterface::PARAMETER_SORT_ORDER, 'desc'],
				[BaseFilterParametersInterface::PARAMETER_ELEMENTS_PAGE, 1],
				[BaseFilterParametersInterface::PARAMETER_ELEMENTS_PER_PAGE, 10]
			]);

		$site = 'example-site';
		$languageModules = ['lang1'];

		$this->headerPreparer->configure($this->filterParametersMock, $site, $languageModules);


		$this->urlBuilderMock->expects($this->once())
			->method('buildFilterUrl')
			->willReturn('http://test.com/sort');

		$this->translatorManagerMock->expects($this->once())
			->method('translate')
			->with($headerFieldMock)
			->willReturn('Translated Column 1');

		$expectedResult = [
			[
				'CONTROL_NAME' => 'column_alternative',
				'if_sortable' => [
					'SORTABLE_ORDER' => '◆',
					'SORT_CONTROL_NAME' => 'column_alternative',
					'LINK_CONTROL_SORT_ORDER' => 'http://test.com/sort',
					'LANG_CONTROL_NAME' => 'Translated Column 1',
				],
			],
		];

		$result = $this->headerPreparer->prepareTableHeader([$headerFieldMock]);

		$this->assertEquals($expectedResult, $result);
	}


	#[Group('units')]
	public function testPrepareTableHeaderReturnsCorrectHeaderForNonSortableField(): void
	{
		$headerFieldMock = $this->createMock(HeaderField::class);
		$headerFieldMock->method('getName')->willReturn('column2');
		$headerFieldMock->method('isSortable')->willReturn(false);

		$this->translatorManagerMock->expects($this->once())
			->method('translate')
			->with($headerFieldMock)
			->willReturn('Translated Column 2');

		$expectedResult = [
			[
				'CONTROL_NAME' => 'column2',
				'LANG_CONTROL_NAME_2' => 'Translated Column 2',
			],
		];

		$result = $this->headerPreparer->prepareTableHeader([$headerFieldMock]);

		$this->assertEquals($expectedResult, $result);
	}

	#[Group('units')]
	public function testPrepareTableHeaderHandlesEmptyInput(): void
	{
		$expectedResult = [];

		$result = $this->headerPreparer->prepareTableHeader([]);

		$this->assertEquals($expectedResult, $result);
	}

}