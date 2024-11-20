<?php

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
