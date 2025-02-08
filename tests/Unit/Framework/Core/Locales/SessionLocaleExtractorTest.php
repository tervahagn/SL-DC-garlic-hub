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

use App\Framework\Core\Locales\LocaleExtractorInterface;
use App\Framework\Core\Locales\SessionLocaleExtractor;
use App\Framework\Core\Session;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use SlimSession\Helper;

class SessionLocaleExtractorTest extends TestCase
{
	private Session $sessionMock;
	private LocaleExtractorInterface $localeExtractor;

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	protected function setUp(): void
	{
		$this->sessionMock = $this->createMock(Session::class);
		$this->localeExtractor = new SessionLocaleExtractor($this->sessionMock, 'en_US');
	}

	#[Group('units')]
	public function testExtractLocaleFromSession()
	{
		$this->sessionMock->method('get')->with('locale', 'en_US')->willReturn('de_DE');

		$whiteList = ['en_US', 'de_DE', 'fr_FR'];
		$result = $this->localeExtractor->extractLocale($whiteList);

		$this->assertEquals('de_DE', $result);
	}

	#[Group('units')]
	public function testExtractLocaleFallbackToDefault()
	{
		$this->sessionMock->method('get')->with('locale', 'en_US')->willReturn(null);

		$whiteList = ['en_US', 'de_DE', 'fr_FR'];
		$result = $this->localeExtractor->extractLocale($whiteList);

		$this->assertEquals('en_US', $result);
	}

	#[Group('units')]
	public function testExtractLocaleFallbackToDefaultForInvalidLocale()
	{
		$this->sessionMock->method('get')->with('locale', 'en_US')->willReturn(['locale' => 'es_ES']);

		$whiteList = ['en_US', 'de_DE', 'fr_FR'];
		$result = $this->localeExtractor->extractLocale($whiteList);

		$this->assertEquals('en_US', $result);
	}
}