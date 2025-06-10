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

namespace App\Framework\Utils\Widget;

use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\BaseSimpleXml;
use SimpleXMLElement;

/**
 * Based on https://www.w3.org/TR/widgets/
 *
 * differences from standard reverse engineered from IAdea
 * xmlns:ia="http://schemas.iawidget.org/widgetconfig-1.0"
 */
class ConfigXML extends BaseSimpleXml
{
	const string DEFAULT_LANGUAGE = 'en';
	const string DEFAULT_DIRECTION = 'ltr';

	protected ?SimpleXMLElement $MyXML = null;
	protected string $default_language = self::DEFAULT_LANGUAGE;
	protected string $default_direction = self::DEFAULT_DIRECTION;
	protected string $id = '';
	protected string $version = '';
	protected string $icon = 'icon.png';
	protected string $content = 'index.html';
	protected array $name = array();
	protected array $description = array();
	protected array $license = array();
	protected array $preferences = array();
	protected array $author = array();


	public function __construct()
	{
	}

	public function getDefaultLanguage(): string
	{
		return $this->default_language;
	}

	public function getDefaultDirection(): string
	{
		return $this->default_direction;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getVersion(): string
	{
		return $this->version;
	}

	public function getContent(): string
	{
		return $this->content;
	}

	public function getName(): array
	{
		return $this->name;
	}

	public function getNameByLanguage(string $lang = self::DEFAULT_LANGUAGE): string
	{
		return $this->checkLanguageKeyOfArray($this->name, $lang);
	}

	public function getDescription(): array
	{
		return $this->description;
	}

	public function getDescriptionByLanguage(string $lang = self::DEFAULT_LANGUAGE): string
	{
		return $this->checkLanguageKeyOfArray($this->description, $lang);
	}

	public function getLicense(): array
	{
		return $this->license;
	}

	public function getLicenseByLanguage($lang = self::DEFAULT_LANGUAGE): string
	{
		return $this->checkLanguageKeyOfArray($this->license, $lang);
	}

	public function getIcon(): string
	{
		return $this->icon;
	}


	public function getAuthor(): array
	{
		return $this->author;
	}

	public function getPreferences(): array
	{
		return $this->preferences;
	}

	/**
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	public function load(string $string): static
	{
		$this->MyXML = $this->loadXmlFromString($string)->getXmlObj();
		if ($this->MyXML->getName() != 'widget')
		{
			throw new FrameworkException('This string is not a config.xml for widgets: '.$string);
		}
		return $this;
	}

	public function parseBasic(): static
	{
		$this->parseDefaultLanguage()
			 ->parseDefaultDirection()
			 ->parseId()
			 ->parseVersion()
			 ->parseAuthor();

		// language depend
		$this->name        = $this->parseName();
		$this->description = $this->parseDescription();
		$this->license     = $this->parseLicense();

		if (isset($this->MyXML->icon['src']))
		{
			$this->icon = (string)$this->MyXML->icon['src'];
		}
		if (isset($this->MyXML->content['src']))
		{
			$this->content = (string)$this->MyXML->content['src'];
		}
		return $this;
	}

	/**
	 * This is the IAdea stuff for adding command-parameters to a widget
	 */
	public function parsePreferences(): static
	{
		if (!isset($this->MyXML->preference))
			return $this;

		$this->preferences = array();

		foreach($this->MyXML->preference as $pref)
		{
			// readonly is only interesting for player not for an authoring tool or CMS
			if (isset($pref['readonly']) && strtolower($pref['readonly']) == 'true')
				continue;
			if (!isset($pref['name']) || empty($pref['name']))
				continue;

			$this->preferences[(string) $pref['name']] = $this->parsePreference($pref);
		}

		return $this;
	}

	public function hasEditablePreferences(): bool
	{
		// this bullshit must be done before due to xpath need to register the default namespace
		// https://www.php.net/manual/de/simplexmlelement.xpath.php#116622
		$this->MyXML->registerXPathNamespace('f', 'http://www.w3.org/ns/widgets');

		$count_preferences = count($this->MyXML->xpath('//f:preference'));
		if ($count_preferences == 0)
			return false;

		$count_readonly    = count ($this->MyXML->xpath('//f:preference[contains(@readonly,\'true\')]'));

		return ($count_preferences-$count_readonly > 0);
	}

	protected function parseDefaultLanguage(): static
	{
		$attr =  $this->MyXML->attributes('xml', true);
		if (isset($attr['lang']))
			$this->default_language = strtolower(substr($attr['lang'], 0, 2));

		return $this;
	}

	protected function parseDefaultDirection(): static
	{
		if (isset($this->MyXML['dir']))
			$this->default_language = $this->MyXML['dir'];

		return $this;
	}

	protected function parseId(): static
	{
		// W3C Style
		if (isset($this->MyXML['id']))
			$this->id =  (string) $this->MyXML['id'];
		else if (isset($this->MyXML->id)) // IAdea style
			$this->id =  str_replace(array('{', '}'), '', (string)$this->MyXML->id);

		return $this;
	}

	protected function parseVersion(): static
	{
		// W3C Style
		if (isset($this->MyXML['version']))
			$this->version = (string) $this->MyXML['version'];
		elseif (isset($this->MyXML->version)) // IAdea style
			$this->version = (string) $this->MyXML->version;

		return $this;
	}

	protected function parseAuthor(): static
	{
		if (!isset($this->MyXML->author))
			$this->author = array();
		if (isset($this->MyXML->author['href']))
			$this->author['href'] = $this->MyXML->author['href'];
		if (isset($this->MyXML->author['email']))
			$this->author['email'] = $this->MyXML->author['email'];

		$this->author['data'] = (string) $this->MyXML->author;
		// Can have children but that is YAGNI
		return $this;
	}

	protected function parseName(): array
	{
		if (!isset($this->MyXML->name))
			return array();

		return $this->parseLanguages($this->MyXML->name);
	}

	protected function parseDescription(): array
	{
		if (!isset($this->MyXML->description))
			return array();

		return $this->parseLanguages($this->MyXML->description);
	}

	protected function parseLicense(): array
	{
		if (!isset($this->MyXML->license))
			return array();

		return $this->parseLanguages($this->MyXML->license);
	}

	protected function parseLanguages(SimpleXMLElement $element): array
	{
		$ret = array();
		foreach($element as $value)
		{
			$attr =  $value->attributes('xml', true);
			$lang = self::DEFAULT_LANGUAGE;
			if (isset($attr['lang']))
				$lang = strtolower(substr((string)$attr['lang'], 0, 2));

			$ret[$lang] = trim((string)$value);
		}

		// make sure that the default language will return a value
		if (!array_key_exists(self::DEFAULT_LANGUAGE, $ret) && count($ret) > 0)
			$ret[self::DEFAULT_LANGUAGE] = array_values($ret)[0];

		return $ret;
	}

	protected function parsePreference(SimpleXMLElement $pref): array
	{
		$ret = array();

		if (isset($pref['value']))
			$ret['value'] = (string) $pref['value'];

		// IAdea stuff
		foreach($pref->attributes('ia', true) as $key => $value)
		{
			$ret[$key] = (string) $value;
		}

		foreach ($pref->children('ia', true) as $child)
		{
			$tag_name = $child->getName();
			switch ($tag_name)
			{
				case 'note':
					if (!empty($ret['notes']))
						$ret['notes'] = array_merge($ret['notes'], $this->generateNotesArray($child));
					else
						$ret['notes'] = $this->generateNotesArray($child);
					continue 2;

				case 'options':
					if ($ret['types'] == 'radio' || $ret['types'] == 'list')
					{
						if (!empty($ret['options']))
							$ret['options'] = array_merge($ret['options'], $this->generateRadioOptionArray($child));
						else
							$ret['options'] = $this->generateRadioOptionArray($child);
					}
					else if ($ret['types'] == 'combo')
					{
						if (!empty($ret['options']))
							$ret['options'] = array_merge($ret['options'], array((string) $child => (string) $child ));
						else
							$ret['options'] = array((string) $child => (string) $child );
					}

					continue 2;
			}

		}
		return $ret;
	}

	/**
	 * @return string
	 */
	protected function getModuleName(): string
	{
		return 'widget_config_xml';
	}

	private function generateNotesArray(SimpleXMLElement $note): array
	{

		$attr = $note->attributes('xml', true);
		$lang = self::DEFAULT_LANGUAGE;
		if (isset($attr['lang']))
			$lang = strtolower(substr((string)$attr['lang'], 0, 2));

		return array($lang => trim((string)$note));

	}

	private function generateRadioOptionArray(SimpleXMLElement $option): array
	{
		$key = '';
		$value = '';
		foreach($option->attributes() as $k => $v)
		{
			if ($k == 'resourceKey')
				$key = (string) $v;
			else if ($k == 'value')
				$value = (string) $v;
		}

		return array($key => $value);
	}

	private function checkLanguageKeyOfArray(array $ar, string $lang): string
	{
		if (empty($ar))
			return '';

		$lang = substr(strtolower($lang), 0, 2);
		if (!array_key_exists($lang, $ar))
			return $ar[self::DEFAULT_LANGUAGE];

		return $ar[$lang];
	}

}