<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);

namespace Tests\Unit\Framework\Utils;

use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\BaseSimpleXml;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class ConcreteBaseSimpleXml extends BaseSimpleXml
{
	/**
	 * @throws ModuleException
	 */
	public function loadXmlFromStringPublic(string $xmlString): void
	{
		$this->loadXmlFromString($xmlString);
	}

	public function setXmlErrorsPublic(): void
	{
		$this->setXmlErrors([]);
	}

	/**
	 * @throws ModuleException
	 */
	public function loadXmlFromFilePublic(string $filePath): void
	{
		$this->loadXmlFromFile($filePath);
	}

	protected function getModuleName(): string
	{
		return 'TestModule';
	}
}

class BaseSimpleXmlTest extends TestCase
{
	private ConcreteBaseSimpleXml $concreteSimpleXml;

	protected function setUp(): void
	{
		parent::setUp();
		$this->concreteSimpleXml = new ConcreteBaseSimpleXml();
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testSetAndGetXmlObj(): void
	{
		$xmlString = '<root><child>value</child></root>';
		$xmlObj = new SimpleXMLElement($xmlString);
		$this->concreteSimpleXml->setXmlObj($xmlObj);
		static::assertSame($xmlObj, $this->concreteSimpleXml->getXmlObj());
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testLoadXmlFromString(): void
	{
		$xmlString = $this->getValidTestXml();
		$this->concreteSimpleXml->loadXmlFromStringPublic($xmlString);
		// @phpstan-ignore-next-line
		static::assertInstanceOf(SimpleXMLElement::class, $this->concreteSimpleXml->getXmlObj());
	}

	#[Group('units')]
	public function testLoadXmlFromStringThrowsException(): void
	{
		$this->expectException(ModuleException::class);
		$this->concreteSimpleXml->loadXmlFromStringPublic('<root><child>value</child>');
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testLoadXmlFromFile(): void
	{
		$filePath = __DIR__ . '/test.xml';
		file_put_contents($filePath, '<root><child>value</child></root>');
		$this->concreteSimpleXml->loadXmlFromFilePublic($filePath);
		// @phpstan-ignore-next-line
		static::assertInstanceOf(SimpleXMLElement::class, $this->concreteSimpleXml->getXmlObj());
		unlink($filePath);
	}

	#[Group('units')]
	public function testLoadXmlFromFileThrowsException(): void
	{
		$this->expectException(ModuleException::class);
		$this->concreteSimpleXml->loadXmlFromFilePublic('nonexistent.xml');
	}

	#[Group('units')]
	public function testgetXmlErrorsAsStringEmpty(): void
	{
		static::assertEmpty($this->concreteSimpleXml->getXmlErrorsAsString());
	}

	#[Group('units')]
	public function testGetXmlErrorArray(): void
	{
		$invalid_xml = $this->getInvalidTestXmlFailedCDATA();

		try
		{
			$exception_thrown = false;

			$this->concreteSimpleXml->loadXmlFromStringPublic($invalid_xml);
		}
		catch (ModuleException $me)
		{
			$exception_thrown = true;
			static::assertEquals('Error reading/parsing xml', $me->getMessage());
			static::assertEquals('TestModule', $me->getModuleName());
		}

		static::assertTrue($exception_thrown);

		$xml_errors_array = $this->concreteSimpleXml->getXmlErrorArray();
		static::assertGreaterThanOrEqual(4, $xml_errors_array);
		foreach($xml_errors_array as $error)
		{
			static::assertEquals(LIBXML_ERR_FATAL, $error->level);
		}

		$xml_error_string = $this->concreteSimpleXml->getXmlErrorsAsString();
		$expected = 'Fatal Error: StartTag: invalid element name Line: 6, Column: 17';
		static::assertEquals($expected, $xml_error_string);

	}

	#[Group('units')]
	public function testLoadXmlFromStringWithInvalidXmlMissingClosingTags(): void
	{
		$invalid_xml = $this->getInvalidTestXmlFailedClosingTags();

		try
		{
			$exception_thrown = false;

			$this->concreteSimpleXml->loadXmlFromStringPublic($invalid_xml);
		}
		catch (ModuleException $me)
		{
			$exception_thrown = true;
			static::assertEquals('Error reading/parsing xml', $me->getMessage());
			static::assertEquals('TestModule', $me->getModuleName());
		}

		static::assertTrue($exception_thrown);

		$xml_errors_array = $this->concreteSimpleXml->getXmlErrorArray();
		static::assertCount(2, $xml_errors_array);

		list($first_error, $second_error) = $xml_errors_array;

		static::assertEquals(LIBXML_ERR_FATAL, $first_error->level);
		static::assertEquals(LIBXML_ERR_FATAL, $second_error->level);

		static::assertEquals('Opening and ending tag mismatch: body line 7 and document', trim($first_error->message));
		// Php 7.4 cries
		//	$this->assertEquals('Premature end of data in tag document line 2', trim($second_error->message));

		$xml_error_string = $this->concreteSimpleXml->getXmlErrorsAsString();
		$expected = 'Fatal Error: Opening and ending tag mismatch: body line 7 and document Line: 9, Column: 13';
		static::assertEquals($expected, $xml_error_string);
	}



	//  Helper

	/**
	 * @return string
	 */
	protected function getValidTestXml(): string
	{
		return <<<XML
<?xml version='1.0'?>
<document>
 <title>Something testable</title>
 <from>Joe</from>
 <to>Jane</to>
 <content><foo><![CDATA[Some raw data within "CDATA"]]></foo></content>
 <body>
 	<inner>I know that's the answer -- but what's the question?</inner>
 </body>
</document>
XML;
	}

	protected function getInvalidTestXmlFailedCDATA(): string
	{
		return <<<XML
<?xml version='1.0'?>
<document>
 <title>Something testable</title>
 <from>Joe</from>
 <to>Jane</to>
 <content><foo><[CDATA[Some raw data within "CDATA"]]></foo></content>
 <body>
 	<inner>I know that's the answer -- but what's the question?</inner>
 </body>
</document>
XML;
	}

	protected function getInvalidTestXmlFailedClosingTags(): string
	{
		return <<<XML
<?xml version='1.0'?>
<document>
 <title>Something testable</title>
 <from>Joe</from>
 <to>Jane</to>
 <content><foo><![CDATA[Some raw data within "CDATA"]]></foo></content>
 <body>
 	<inner>I know that's the answer -- but what's the question?</inner>
 </document>
XML;
	}

}
