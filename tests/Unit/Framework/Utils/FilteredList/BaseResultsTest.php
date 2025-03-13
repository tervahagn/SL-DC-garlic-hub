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


namespace Tests\Unit\Framework\Utils\FilteredList;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FilteredList\BaseResults;
use App\Framework\Utils\FilteredList\HeaderField;
use App\Framework\Utils\FilteredList\HeaderFieldFactory;
use App\Framework\Utils\FormParameters\BaseParameters;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;

class ConcreteBaseResults extends BaseResults{ }

class BaseResultsTest extends TestCase
{
	private ConcreteBaseResults $baseResults;
	private BaseParameters $baseParametersMock;
	private Translator $translatorMock;
	private HeaderField $headerFieldMock;
	protected HeaderFieldFactory $headerFieldFactoryMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->baseParametersMock      = $this->createMock(BaseParameters::class);
		$this->translatorMock          = $this->createMock(Translator::class);
		$this->headerFieldFactoryMock  = $this->createMock(HeaderFieldFactory::class);

		$this->baseResults        = new ConcreteBaseResults($this->headerFieldFactoryMock);
	}

	#[Group('units')]
	public function testSetSite(): void
	{
		$site = 'testSite';
		$result = $this->baseResults->setSite($site);

		$this->assertSame($this->baseResults, $result);
	}

	#[Group('units')]
	public function testGetTplData(): void
	{
		// Standardwert sollte ein leeres Array sein
		$this->assertSame([], $this->baseResults->getTplData());
	}

	#[Group('units')]
	public function testSetTranslator(): void
	{
		$result = $this->baseResults->setTranslator($this->translatorMock);

		$this->assertSame($this->baseResults, $result);
	}

	#[Group('units')]
	public function testGetAndSetCurrentTotalResult(): void
	{
		$testValue = 42;

		$result = $this->baseResults->setCurrentTotalResult($testValue);

		$this->assertSame($this->baseResults, $result);
		$this->assertSame($testValue, $this->baseResults->getCurrentTotalResult());
	}

	#[Group('units')]
	public function testGetAndSetCurrentFilterResults(): void
	{
		$testArray = ['test1', 'test2'];

		$result = $this->baseResults->setCurrentFilterResults($testArray);

		$this->assertSame($this->baseResults, $result);
		$this->assertSame($testArray, $this->baseResults->getCurrentFilterResults());
	}

	#[Group('units')]
	public function testAddAdditionalUrlParameter(): void
	{
		$key = 'testKey';
		$value = 'testValue';

		$result = $this->baseResults->addAdditionalUrlParameter($key, $value);

		$this->assertSame($this->baseResults, $result);
		$this->assertTrue($this->baseResults->hasAdditionalUrlParameters());
	}

	#[Group('units')]
	public function testClearAdditionalUrlParameters(): void
	{
		$this->baseResults->addAdditionalUrlParameter('testKey', 'testValue');
		$this->assertTrue($this->baseResults->hasAdditionalUrlParameters());

		$result = $this->baseResults->clearAdditionalUrlParameters();

		$this->assertSame($this->baseResults, $result);
		$this->assertFalse($this->baseResults->hasAdditionalUrlParameters());
	}

	#[Group('units')]
	public function testHasAdditionalUrlParameters(): void
	{
		$this->assertFalse($this->baseResults->hasAdditionalUrlParameters());

		$this->baseResults->addAdditionalUrlParameter('testKey', 'testValue');
		$this->assertTrue($this->baseResults->hasAdditionalUrlParameters());
	}

	#[Group('unicts')]
	public function testRenderTableHeader(): void
	{
		$this->headerFieldMock->method('getName')->willReturn('testField');
		$this->headerFieldMock->method('isSortable')->willReturn(false);

		// Methode mock für renderNonSortableHeaderField
		$this->baseResults->expects($this->once())
			->method('renderNonSortableHeaderField')
			->with($this->headerFieldMock)
			->willReturn('translatedField');

		// Reflection verwenden um tableHeaderFields zu setzen
		$reflection = new \ReflectionClass($this->baseResults);
		$property = $reflection->getProperty('tableHeaderFields');
		$property->setValue($this->baseResults, [$this->headerFieldMock]);

		$result = $this->baseResults->renderTableHeader(
			$this->baseParametersMock,
			'testSite',
			$this->translatorMock
		);

		$this->assertIsArray($result);
		$this->assertCount(1, $result);
		$this->assertSame('testField', $result[0]['CONTROL_NAME']);
	}



	#[Group('units')]
	public function testGetTableHeaderFields(): void
	{
		$this->assertSame([], $this->baseResults->getTableHeaderFields());

		$this->baseResults->setTableHeaderFields(['hurz']);

		$this->assertCount(1, $this->baseResults->getTableHeaderFields());
		$this->assertSame(['hurz'], $this->baseResults->getTableHeaderFields());
	}

	#[Group('units')]
	public function testCreateField(): void
	{
		$field = $this->baseResults->createField();

		$this->assertInstanceOf(HeaderField::class, $field);

		$this->assertCount(1, $this->baseResults->getTableHeaderFields());
	}

	#[Group('units')]
	public function testAddLanguageModule(): void
	{
		$moduleName = 'testModule';

		$result = $this->baseResults->addLanguageModule($moduleName);

		$this->assertSame($this->baseResults, $result);

		// Prüfen ob das Modul hinzugefügt wurde (über Reflection)
		$reflection = new \ReflectionClass($this->baseResults);
		$property = $reflection->getProperty('languageModules');
		$languageModules = $property->getValue($this->baseResults);

		$this->assertCount(1, $languageModules);
		$this->assertSame($moduleName, $languageModules[0]);
	}

}
