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


namespace Tests\Unit\Framework\Utils\Widget;

use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Widget\ConfigXML;
use App\Modules\Mediapool\Utils\MimeTypeDetector;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ConfigXMLTest extends TestCase
{
	private string $baseDirectory;

	protected function setUp(): void
	{
		$this->baseDirectory = getenv('TEST_BASE_DIR') . '/resources/widgets';
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testEmptyString()
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
	public function testWrongXMLString()
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
	public function testParseBasicsDefaultsOnly()
	{
		$xml_string = '<?xml version="1.0" encoding="UTF-8"?><widget></widget>';
		$TestClass = new ConfigXML();

		$TestClass->load($xml_string)->parseBasic();

		$this->assertEquals('', $TestClass->getId());
		$this->assertEquals('', $TestClass->getNameByLanguage());
		$this->assertEquals('icon.png', $TestClass->getIcon());
		$this->assertEquals('index.html', $TestClass->getContent());
		$this->assertEquals('en', $TestClass->getDefaultLanguage());
		$this->assertEquals('ltr', $TestClass->getDefaultDirection());
		$this->assertEquals('', $TestClass->getVersion());
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testParseBasicsStandard()
	{
		$TestClass = new ConfigXML();

		$TestClass->load($this->getBasicStandard())->parseBasic();

		$this->assertEquals('http://example.org/exampleWidget', $TestClass->getId());
		$this->assertEquals('Standard Basic Widget Configuration', $TestClass->getNameByLanguage());
		$this->assertEquals('de', $TestClass->getDefaultLanguage());
		$this->assertEquals('2.0 Beta', $TestClass->getVersion());
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testParseBasicsIAdea()
	{
		$TestClass = new ConfigXML();

		$TestClass->load($this->getBasicIAdea())->parseBasic();

		$this->assertEquals('123456-7890-IAdea', $TestClass->getId());
		$this->assertEquals('IAdea Basic Widget Configuration', $TestClass->getNameByLanguage());
		$this->assertEquals('2.0.0.0', $TestClass->getVersion());
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testWithUnavailableDefaultLanguage()
	{
		$TestClass = new ConfigXML();

		$TestClass->load($this->getNoDefaultLanguage())->parseBasic();
		$this->assertEquals('Nicht so, mein Freund!', $TestClass->getNameByLanguage());
		$this->assertEquals('Nicht so, mein Freund!', $TestClass->getNameByLanguage('de-De'));
		$this->assertEquals('Nicht so, mein Freund!', $TestClass->getNameByLanguage('de'));
		$this->assertEquals('Όχι έτσι, βρε φίλε μου!', $TestClass->getNameByLanguage('el'));
		$this->assertEquals('Όχι έτσι, βρε φίλε μου!', $TestClass->getNameByLanguage('el_should_check only_first_letters'));
		$this->assertEquals('Nicht so, mein Freund!', $TestClass->getNameByLanguage('fallback'));
		$this->assertCount(3, $TestClass->getName());
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testAnalyzeFullStandard()
	{
		$xml_string = file_get_contents($this->baseDirectory.'/standard_config.xml');
		$TestClass = new ConfigXML();
		$TestClass->load($xml_string)->parseBasic();

		$this->assertEquals('http://example.org/exampleWidget', $TestClass->getId());
		$this->assertEquals('en', $TestClass->getDefaultLanguage());
		$this->assertEquals('2.0', $TestClass->getVersion());
		$this->assertEquals('start.html', $TestClass->getContent());
		$this->assertEquals('icon.jpg', $TestClass->getIcon());

		$this->assertEquals('The example Widget!', $TestClass->getNameByLanguage());
		$this->assertEquals('The example Widget!', $TestClass->getNameByLanguage('en'));
		$this->assertEquals('Ein Beispiel Widget!', $TestClass->getNameByLanguage('de'));
		$this->assertCount(2, $TestClass->getName());

		$this->assertEquals('English description.', $TestClass->getDescriptionByLanguage());
		$this->assertEquals('English description.', $TestClass->getDescriptionByLanguage('en'));
		$this->assertEquals('Deutsche Beschreibung.', $TestClass->getDescriptionByLanguage('de'));
		$this->assertCount(2, $TestClass->getDescription());

		$this->assertEquals('English license', $TestClass->getLicenseByLanguage());
		$this->assertEquals('English license', $TestClass->getLicenseByLanguage('en'));
		$this->assertEquals('Deutsche Lizenz', $TestClass->getLicenseByLanguage('de'));
		$this->assertCount(2, $TestClass->getLicense());
		$expected_author = array('href' => 'http://foo-bar.example.org/',
			'email' => 'foo-bar@example.org',
			'data'  => 'Foo Bar Corp'
		);
		$this->assertEquals($expected_author, $TestClass->getAuthor());

		$TestClass->parsePreferences();
		$expect_preference = array('url' => array());
		$this->assertEquals($expect_preference, $TestClass->getPreferences());

	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testAnalyzeFullIAdea()
	{
		$xml_string = file_get_contents($this->baseDirectory.'/iadea_config.xml');
		$TestClass = new ConfigXML();
		$TestClass->load($xml_string)->parseBasic();

		$this->assertEquals('4679064D-0C36-4FCB-97EE-F9A00F8D77C2', $TestClass->getId());
		$this->assertEquals('en', $TestClass->getDefaultLanguage());
		$this->assertEquals('1.0.0.0', $TestClass->getVersion());
		$this->assertEquals('index.html', $TestClass->getContent());
		$this->assertEquals('icon.png', $TestClass->getIcon());

		$this->assertEquals('Rss', $TestClass->getNameByLanguage());
		$this->assertCount(1, $TestClass->getName());

		$TestClass->parsePreferences();
		$preferences = $TestClass->getPreferences();
		$this->assertCount(10, $preferences);

		$expected = array('types' => 'text', 'mandatory' => 'true', 'tooltip' => 'e.g. http://rss.cnn.com/rss/cnn_topstories.rss');
		$this->assertEquals($expected, $preferences['urls']);

		$expected = array('types' => 'radio', 'default' => 'ltr', 'options' => array('ltr' => 'ltr', 'rtl' => 'rtl'));
		$this->assertEquals($expected, $preferences['WritingDirection']);

		$expected = array('types' => 'checkbox', 'default' => 'true');
		$this->assertEquals($expected, $preferences['showSweepHand']);

		$expected = array('types' => 'color', 'default' => '#008080', 'previewbg' => 'true');
		$this->assertEquals($expected, $preferences['BackgroundColor']);

		$expected = array('types' => 'colorOpacity', 'value' => '100');
		$this->assertEquals($expected, $preferences['BackgroundOpacity']);

		$expected = array('types' => 'color', 'default' => '#ffffff');
		$this->assertEquals($expected, $preferences['Color']);

		$expected = array('types' => 'integer', 'value' => '', 'notes' => array('en' => 'Control the length of the RSS title.', 'de' => 'Steuert die Länge des RSS-Titels.'));
		$this->assertEquals($expected, $preferences['Title-Length']);

		$expected = array('types' => 'integer', 'value' => '', 'notes' => array('en' => 'Control the length of the RSS content. 0 or non-integer words will display the full length of the content.'));
		$this->assertEquals($expected, $preferences['Content-Length']);

		$expected = array('types' => 'combo',
			'mandatory' => 'true',
			'default' => 'New York',
			'options' => array('Athens'=>'Athens','Hamburg'=>'Hamburg','London'=>'London','New York'=>'New York','Beijing'=>'Beijing','Paris'=>'Paris','Sao Paulo'=>'Sao Paulo','Taipei'=>'Taipei','Tokyo'=>'Tokyo'));
		$this->assertEquals($expected, $preferences['cities']);

		$expected = array('types' => 'list', 'default' => 'right', 'options' => array('right' => 'right', 'center' => 'center', 'left' => 'left'));
		$this->assertEquals($expected, $preferences['align']);
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testHasEditablePreferencesTrue()
	{
		$xml_string = file_get_contents($this->baseDirectory.'/iadea_config.xml');
		$TestClass = new ConfigXML();

		$this->assertTrue($TestClass->load($xml_string)->hasEditablePreferences());
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testHasEditablePreferencesFalse()
	{
		$xml_string = $this->getReadOnly();
		$TestClass = new ConfigXML();

		$this->assertFalse($TestClass->load($xml_string)->hasEditablePreferences());
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testHasEditablePreferencesWithNoPreference()
	{
		$xml_string = $this->getNoDefaultLanguage();
		$TestClass = new ConfigXML();

		$this->assertFalse($TestClass->load($xml_string)->hasEditablePreferences());
	}


// ======================== helper functions =======================

	private function getReadOnly(): string
	{
		return '<widget xmlns = "http://www.w3.org/ns/widgets">
			 <preference name="count" value="255" ia:types="integer" readonly="true" />
			 <preference name="noTransform" value="true" readonly="true"/>
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
