<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

namespace Tests\Unit\Framework\Core\Locales;

use App\Framework\Core\Locales\UrlLocaleExtractor;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class UrlLocaleExtractorTest extends TestCase
{
	private UrlLocaleExtractor $localeExtractor;

	protected function setUp(): void
	{
		$this->localeExtractor = new UrlLocaleExtractor('en_US');
	}

	#[Group('units')]
	public function testExtractLocaleWithValidLocale(): void
	{
		$_GET['locale'] = 'de-DE';

		$result = $this->localeExtractor->extractLocale(['en_US', 'de_DE']);

		$this->assertSame('de_DE', $result);
	}

	#[Group('units')]
	public function testExtractLocaleWithInvalidLocale(): void
	{
		$_GET['locale'] = 'fr-FR';

		$result = $this->localeExtractor->extractLocale(['en_US', 'de_DE']);

		$this->assertSame('en_US', $result);
	}

	#[Group('units')]
	public function testExtractLocaleWithNoLocaleInUrl(): void
	{
		unset($_GET['locale']);

		$result = $this->localeExtractor->extractLocale(['en_US', 'de_DE']);

		$this->assertSame('en_US', $result);
	}

	#[Group('units')]
	public function testExtractLocaleConvertsDashToUnderscore(): void
	{
		$_GET['locale'] = 'en-us';

		$result = $this->localeExtractor->extractLocale(['en_US', 'de_DE']);

		$this->assertSame('en_US', $result);
	}
}
