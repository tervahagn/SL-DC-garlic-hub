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

namespace Tests\Unit\Framework\Utils\Widget;

use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Widget\ConfigXML;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ConfigXMLTest extends TestCase
{
	private string $baseDirectory;

	protected function setUp(): void
	{
		parent::setUp();
		$this->baseDirectory = getenv('TEST_BASE_DIR') . '/resources/widgets';
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testEmptyString(): void
	{
		$TestClass = new ConfigXML();
		$this->expectException('App\Framework\Exceptions\ModuleException');
		$this->expectExceptionMessage('Error reading/parsing xml');

		$TestClass->load('');
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testWrongXMLString(): void
	{
		$xml_string = '<?xml version="1.0" encoding="UTF-8"?><not_widget></not_widget>';
		$TestClass  = new ConfigXML();
		$this->expectException('App\Framework\Exceptions\FrameworkException');
		$this->expectExceptionMessage('This string is not a config.xml for widgets: '.$xml_string);

		$TestClass->load($xml_string);
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testParseBasicsDefaultsOnly(): void
	{
		$xml_string = '<?xml version="1.0" encoding="UTF-8"?><widget></widget>';
		$TestClass = new ConfigXML();

		$TestClass->load($xml_string)->parseBasic();

		static::assertEquals('', $TestClass->getId());
		static::assertEquals('', $TestClass->getNameByLanguage());
		static::assertEquals('icon.png', $TestClass->getIcon());
		static::assertEquals('index.html', $TestClass->getContent());
		static::assertEquals('en', $TestClass->getDefaultLanguage());
		static::assertEquals('ltr', $TestClass->getDefaultDirection());
		static::assertEquals('', $TestClass->getVersion());
	}

	/**
	 */
	#[Group('units')]
	public function testParseBasicsNoXML(): void
	{
		$TestClass = new ConfigXML();

		$TestClass->parseBasic();

		static::assertEquals('', $TestClass->getId());
		static::assertEquals('', $TestClass->getNameByLanguage());
		static::assertEquals('icon.png', $TestClass->getIcon());
		static::assertEquals('index.html', $TestClass->getContent());
		static::assertEquals('en', $TestClass->getDefaultLanguage());
		static::assertEquals('ltr', $TestClass->getDefaultDirection());
		static::assertEquals('', $TestClass->getVersion());
	}


	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testParseBasicsStandard(): void
	{
		$TestClass = new ConfigXML();

		$TestClass->load($this->getBasicStandard())->parseBasic();

		static::assertEquals('http://example.org/exampleWidget', $TestClass->getId());
		static::assertEquals('Standard Basic Widget Configuration', $TestClass->getNameByLanguage());
		static::assertEquals('de', $TestClass->getDefaultLanguage());
		static::assertEquals('2.0 Beta', $TestClass->getVersion());
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testParseBasicsIAdea(): void
	{
		$TestClass = new ConfigXML();

		$TestClass->load($this->getBasicIAdea())->parseBasic();

		static::assertEquals('123456-7890-IAdea', $TestClass->getId());
		static::assertEquals('IAdea Basic Widget Configuration', $TestClass->getNameByLanguage());
		static::assertEquals('2.0.0.0', $TestClass->getVersion());
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testWithUnavailableDefaultLanguage(): void
	{
		$TestClass = new ConfigXML();

		$TestClass->load($this->getNoDefaultLanguage())->parseBasic();
		static::assertEquals('Nicht so, mein Freund!', $TestClass->getNameByLanguage());
		static::assertEquals('Nicht so, mein Freund!', $TestClass->getNameByLanguage('de-De'));
		static::assertEquals('Nicht so, mein Freund!', $TestClass->getNameByLanguage('de'));
		static::assertEquals('Όχι έτσι, βρε φίλε μου!', $TestClass->getNameByLanguage('el'));
		static::assertEquals('Όχι έτσι, βρε φίλε μου!', $TestClass->getNameByLanguage('el_should_check only_first_letters'));
		static::assertEquals('Nicht so, mein Freund!', $TestClass->getNameByLanguage('fallback'));
		static::assertCount(3, $TestClass->getName());
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testAnalyzeFullStandard(): void
	{
		$xml_string = file_get_contents($this->baseDirectory.'/standard_config.xml');
		if ($xml_string === false)
			static::markTestSkipped('Could not read file');

		$TestClass = new ConfigXML();
		$TestClass->load($xml_string)->parseBasic();

		static::assertEquals('http://example.org/exampleWidget', $TestClass->getId());
		static::assertEquals('en', $TestClass->getDefaultLanguage());
		static::assertEquals('2.0', $TestClass->getVersion());
		static::assertEquals('start.html', $TestClass->getContent());
		static::assertEquals('icon.jpg', $TestClass->getIcon());

		static::assertEquals('The example Widget!', $TestClass->getNameByLanguage());
		static::assertEquals('The example Widget!', $TestClass->getNameByLanguage('en'));
		static::assertEquals('Ein Beispiel Widget!', $TestClass->getNameByLanguage('de'));
		static::assertCount(2, $TestClass->getName());

		static::assertEquals('English description.', $TestClass->getDescriptionByLanguage());
		static::assertEquals('English description.', $TestClass->getDescriptionByLanguage('en'));
		static::assertEquals('Deutsche Beschreibung.', $TestClass->getDescriptionByLanguage('de'));
		static::assertCount(2, $TestClass->getDescription());

		static::assertEquals('English license', $TestClass->getLicenseByLanguage());
		static::assertEquals('English license', $TestClass->getLicenseByLanguage('en'));
		static::assertEquals('Deutsche Lizenz', $TestClass->getLicenseByLanguage('de'));
		static::assertCount(2, $TestClass->getLicense());
		$expected_author = ['href' => 'http://foo-bar.example.org/',
			'email' => 'foo-bar@example.org',
			'data'  => 'Foo Bar Corp'
		];
		static::assertEquals($expected_author, $TestClass->getAuthor());

		$TestClass->parsePreferences();
		$expect_preference = ['url' => []];
		static::assertEquals($expect_preference, $TestClass->getPreferences());
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testAnalyzeFullIAdea(): void
	{
		$xml_string = file_get_contents($this->baseDirectory.'/iadea_config.xml');
		if ($xml_string === false)
			static::markTestSkipped('Could not read file');

		$TestClass = new ConfigXML();
		$TestClass->load($xml_string)->parseBasic();

		static::assertEquals('4679064D-0C36-4FCB-97EE-F9A00F8D77C2', $TestClass->getId());
		static::assertEquals('en', $TestClass->getDefaultLanguage());
		static::assertEquals('1.0.0.0', $TestClass->getVersion());
		static::assertEquals('index.html', $TestClass->getContent());
		static::assertEquals('icon.png', $TestClass->getIcon());

		static::assertEquals('Rss', $TestClass->getNameByLanguage());
		static::assertCount(1, $TestClass->getName());

		$TestClass->parsePreferences();
		$preferences = $TestClass->getPreferences();
		static::assertCount(10, $preferences);

		$expected = ['types' => 'text', 'mandatory' => 'true', 'tooltip' => 'e.g. http://rss.cnn.com/rss/cnn_topstories.rss'];
		static::assertEquals($expected, $preferences['urls']);

		$expected = ['types' => 'radio', 'default' => 'ltr', 'options' => ['ltr' => 'ltr', 'rtl' => 'rtl']];
		static::assertEquals($expected, $preferences['WritingDirection']);

		$expected = ['types' => 'checkbox', 'default' => 'true'];
		static::assertEquals($expected, $preferences['showSweepHand']);

		$expected = ['types' => 'color', 'default' => '#008080', 'previewbg' => 'true'];
		static::assertEquals($expected, $preferences['BackgroundColor']);

		$expected = ['types' => 'colorOpacity', 'value' => '100'];
		static::assertEquals($expected, $preferences['BackgroundOpacity']);

		$expected = ['types' => 'color', 'default' => '#ffffff'];
		static::assertEquals($expected, $preferences['Color']);

		$expected = ['types' => 'integer', 'value' => '', 'notes' => ['en' => 'Control the length of the RSS title.', 'de' => 'Steuert die Länge des RSS-Titels.']];
		static::assertEquals($expected, $preferences['Title-Length']);

		$expected = ['types' => 'integer', 'value' => '', 'notes' => ['en' => 'Control the length of the RSS content. 0 or non-integer words will display the full length of the content.']];
		static::assertEquals($expected, $preferences['Content-Length']);

		$expected = ['types' => 'combo',
			'mandatory' => 'true',
			'default' => 'New York',
			'options' => ['Athens'=>'Athens','Hamburg'=>'Hamburg','London'=>'London','New York'=>'New York','Beijing'=>'Beijing','Paris'=>'Paris','Sao Paulo'=>'Sao Paulo','Taipei'=>'Taipei','Tokyo'=>'Tokyo']];
		static::assertEquals($expected, $preferences['cities']);

		$expected = ['types' => 'list', 'default' => 'right', 'options' => ['right' => 'right', 'center' => 'center', 'left' => 'left']];
		static::assertEquals($expected, $preferences['align']);
	}

	/**
	 * @throws ModuleException
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testParsePreferencesNoNaming(): void
	{
		$xml_string = '<?xml version="1.0" encoding="UTF-8"?>
		<widget xmlns       = "http://www.w3.org/ns/widgets"
				id          = "http://example.org/exampleWidget"
				viewmodes   = "fullscreen"></widget>';

		$TestClass = new ConfigXML();
		$TestClass->load($xml_string)->parseBasic();

		$TestClass->parsePreferences();
		static::assertEmpty($TestClass->getPreferences());
	}

	/**
	 * @throws ModuleException
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testParsePreferencesWithNoName(): void
	{
		$xml_string = '<?xml version="1.0" encoding="UTF-8"?>
		<widget xmlns       = "http://www.w3.org/ns/widgets"
				id          = "http://example.org/exampleWidget"
				viewmodes   = "fullscreen">
				<name short="Example 2.0" xml:lang="en-us">The example Widget!</name>
     <preference value="ea31ad3a23fd2f" readonly="false" />

</widget>';


		$TestClass = new ConfigXML();
		$TestClass->load($xml_string)->parseBasic();

		$TestClass->parsePreferences();
		static::assertEmpty($TestClass->getPreferences());

	}

	/**
	 * @throws ModuleException
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testParseDefaultDirection(): void
	{
		$xml_string = '<?xml version="1.0" encoding="UTF-8"?>
<widget xmlns="http://www.w3.org/ns/widgets" dir="rtl" xml:lang="fa">
	<name short="برنامه">برنامه</name>

     <preference value="ea31ad3a23fd2f" readonly="false" />

</widget>';
		$TestClass = new ConfigXML();
		$TestClass->load($xml_string)->parseBasic();

		$TestClass->parsePreferences();
		static::assertEmpty($TestClass->getPreferences());
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testHasEditablePreferencesTrue(): void
	{
		$xml_string = file_get_contents($this->baseDirectory.'/iadea_config.xml');
		if ($xml_string === false)
		{
			static::markTestSkipped('Could not read file');
		}

		$TestClass = new ConfigXML();

		static::assertTrue($TestClass->load($xml_string)->hasEditablePreferences());
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testHasEditablePreferencesFalse(): void
	{
		$xml_string = $this->getReadOnly();
		$TestClass = new ConfigXML();

		static::assertFalse($TestClass->load($xml_string)->hasEditablePreferences());
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testHasNoPreferences(): void
	{
		$xml_string = $this->getBasicStandard();
		$TestClass = new ConfigXML();

		static::assertFalse($TestClass->load($xml_string)->hasEditablePreferences());
	}

	#[Group('units')]
	public function testHasEditablePreferencesFalseXMLNull(): void
	{
		$TestClass = new ConfigXML();

		static::assertFalse($TestClass->hasEditablePreferences());
	}


	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testHasEditablePreferencesWithNoReadOnlyPreference(): void
	{
		$xml_string = $this->getNoReadOnly();
		$TestClass = new ConfigXML();

		static::assertTrue($TestClass->load($xml_string)->hasEditablePreferences());
	}



// ======================== helper functions =======================

	private function getReadOnly(): string
	{
		return '<widget xmlns = "http://www.w3.org/ns/widgets">
			 <preference name="count" value="255" ia:types="integer" readonly="true" />
			 <preference name="noTransform" value="true" readonly="true"/>
        </widget>';
	}

	private function getNoReadOnly(): string
	{
		return '<widget xmlns = "http://www.w3.org/ns/widgets">
			 <preference name="count" value="255" ia:types="integer" />
			 <preference name="noTransform" value="true"/>
        </widget>';
	}


	private function getNoDefaultLanguage(): string
	{
		return '<widget xmlns = "http://www.w3.org/ns/widgets">
			 <name xml:lang="de" short="Nicht so">Nicht so, mein Freund!</name>
			 <name xml:lang="el" short="Όχι βρε">Όχι έτσι, βρε φίλε μου!</name>
        </widget>';
	}

	private function getBasicStandard(): string
	{
		return '<widget xmlns       = "http://www.w3.org/ns/widgets"
        id          = "http://example.org/exampleWidget"
        version     = "2.0 Beta"
        xml:lang=   "de-DE">
			 <name short="standard">Standard Basic Widget Configuration</name>
        </widget>';
	}

	private function getBasicIAdea(): string
	{
		return '<?xml version="1.0" encoding="UTF-8"?>
<widget xmlns="http://www.w3.org/ns/widgets">
 <name short="nothing">IAdea Basic Widget Configuration</name>
 <id>{123456-7890-IAdea}</id>
 <version>2.0.0.0</version>
</widget>';
	}

}
