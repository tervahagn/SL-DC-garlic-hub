<?php

namespace Tests\Unit\Framework\Utils\Datatable;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Datatable\AbstractDatatablePreparer;
use App\Framework\Utils\Datatable\PrepareService;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Framework\Utils\FormParameters\BaseFilterParametersInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ConcreteDatatablePreparer extends AbstractDatatablePreparer
{
	public function prepareTableBody(array $currentFilterResults, array $fields, $currentUID): array {return [];}
}
class AbstractDatatablePreparerTest extends TestCase
{
	private AbstractDatatablePreparer $datatablePreparer;
	private PrepareService $prepareServiceMock;
	private BaseFilterParameters $parametersMock;

	protected function setUp(): void
	{
		$this->prepareServiceMock = $this->createMock(PrepareService::class);
		$this->parametersMock = $this->createMock(BaseFilterParameters::class);
		$this->datatablePreparer = new ConcreteDatatablePreparer('TestModule', $this->prepareServiceMock, $this->parametersMock);
	}

	#[Group('units')]
	public function testPrepareFilterForm()
	{
		$params = ['test' => 'test'];
		$this->prepareServiceMock->expects($this->once())->method('prepareForm')->with($params);

		$this->datatablePreparer->prepareFilterForm($params);
	}

	#[Group('units')]
	public function testPreparePagination()
	{
		$params1 = ['test1' => 'test1'];
		$params2 = ['test2' => 'test2'];
		$this->prepareServiceMock->expects($this->once())->method('preparePagination')->with($params1, $params2);

		$this->datatablePreparer->preparePagination($params1, $params2);
	}

	#[Group('units')]
	public function testPrepareTableHeader()
	{
		$params1 = ['test1' => 'test1', 'test2' => 'test2'];
		$params2 = ['lang1', 'lang22'];
		$this->prepareServiceMock->expects($this->once())->method('prepareDatatableHeader')->with($params1, $params2);

		$this->datatablePreparer->prepareTableHeader($params1, $params2);
	}

	#[Group('units')]
	public function testPrepareAddWithoutParams()
	{
		$translatorMock = $this->createMock(Translator::class);
		$this->datatablePreparer->setTranslator($translatorMock);

		$translatorMock->expects($this->once())->method('translate')
			->with('add', 'TestModule')->willReturn('test');

		$expected = [
			'ADD_BI_ICON' => 'folder-plus',
			'LANG_ELEMENTS_ADD_LINK' => 'test',
			'ELEMENTS_ADD_LINK' => '#'
		];

		$result = $this->datatablePreparer->prepareAdd();

		$this->assertSame($expected, $result);
	}

	#[Group('units')]
	public function testPrepareAddWithParam()
	{
		$translatorMock = $this->createMock(Translator::class);
		$this->datatablePreparer->setTranslator($translatorMock);

		$translatorMock->expects($this->once())->method('translate')
			->with('add', 'TestModule')->willReturn('test2');

		$expected = [
			'ADD_BI_ICON' => 'hurz',
			'LANG_ELEMENTS_ADD_LINK' => 'test2',
			'ELEMENTS_ADD_LINK' => '#'
		];

		$result = $this->datatablePreparer->prepareAdd('hurz');

		$this->assertSame($expected, $result);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testPrepareSort()
	{

		$this->parametersMock
			->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParametersInterface::PARAMETER_SORT_COLUMN, 'current_column'],
				[BaseFilterParametersInterface::PARAMETER_SORT_ORDER, 'asc|desc']
			]);


		$expected = [
			'column' => 'current_column',
			'order' => 'asc|desc',
		];

		$result = $this->datatablePreparer->prepareSort();

		$this->assertSame($expected, $result);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testPreparePage()
	{
		$this->parametersMock
			->method('getValueOfParameter')
			->willReturnMap([
				[BaseFilterParametersInterface::PARAMETER_ELEMENTS_PAGE, 'current_page'],
				[BaseFilterParametersInterface::PARAMETER_ELEMENTS_PER_PAGE, 100]
			]);

		$expected = [
			'current' => 'current_page',
			'num_elements' => 100,
		];

		$result = $this->datatablePreparer->preparePage();

		$this->assertSame($expected, $result);
	}
}