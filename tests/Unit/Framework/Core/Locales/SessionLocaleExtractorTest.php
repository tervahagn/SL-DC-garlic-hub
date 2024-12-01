<?php

namespace Tests\Unit\Framework\Core\Locales;

use App\Framework\Core\Locales\SessionLocaleExtractor;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SlimSession\Helper;

class SessionLocaleExtractorTest extends TestCase
{
	private $sessionMock;
	private $localeExtractor;

	#[Group('units')]
	protected function setUp(): void
	{
		$this->sessionMock = $this->createMock(Helper::class);
		$this->localeExtractor = new SessionLocaleExtractor($this->sessionMock, 'en');
	}

	#[Group('units')]
	public function testExtractLocaleFromSession()
	{
		$this->sessionMock->method('get')->with('user')->willReturn(['locale' => 'de']);

		$whiteList = ['en', 'de', 'fr'];
		$result = $this->localeExtractor->extractLocale($whiteList);

		$this->assertEquals('de', $result);
	}

	#[Group('units')]
	public function testExtractLocaleFallbackToDefault()
	{
		$this->sessionMock->method('get')->with('user')->willReturn(null);

		$whiteList = ['en', 'de', 'fr'];
		$result = $this->localeExtractor->extractLocale($whiteList);

		$this->assertEquals('en', $result);
	}

	#[Group('units')]
	public function testExtractLocaleFallbackToDefaultForInvalidLocale()
	{
		$this->sessionMock->method('get')->with('user')->willReturn(['locale' => 'es']);

		$whiteList = ['en', 'de', 'fr'];
		$result = $this->localeExtractor->extractLocale($whiteList);

		$this->assertEquals('en', $result);
	}
}