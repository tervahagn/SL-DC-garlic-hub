<?php

namespace Tests\Unit\Framework\Utils\Datatable\Results;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Utils\Datatable\Results\HeaderField;
use App\Framework\Utils\Datatable\Results\TranslatorManager;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class TranslatorManagerTest extends TestCase
{
	private Translator $translator;
	private TranslatorManager $translatorManager;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->translator = $this->createMock(Translator::class);
		$this->translatorManager = new TranslatorManager($this->translator);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testTranslateShouldReturnEmptyStringWhenTranslationIsSkipped(): void
	{
		$headerField = $this->createMock(HeaderField::class);
		$headerField->method('shouldSkipTranslation')->willReturn(true);

		$result = $this->translatorManager->translate($headerField);

		$this->assertSame('', $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testTranslateShouldUseSpecificLangModuleWhenProvided(): void
	{
		$headerField = $this->createMock(HeaderField::class);
		$headerField->method('shouldSkipTranslation')->willReturn(false);
		$headerField->method('hasSpecificLangModule')->willReturn(true);
		$headerField->method('getSpecificLanguageModule')->willReturn('specific_lang_module');
		$headerField->method('getName')->willReturn('key');

		$this->translator->expects($this->once())
			->method('translate')
			->with('key', 'specific_lang_module')
			->willReturn('translated_value');

		$result = $this->translatorManager->translate($headerField);

		$this->assertSame('translated_value', $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testTranslateShouldIterateOverLanguageModulesAndReturnFirstTranslatedValue(): void
	{
		$headerField = $this->createMock(HeaderField::class);
		$headerField->method('shouldSkipTranslation')->willReturn(false);
		$headerField->method('hasSpecificLangModule')->willReturn(false);
		$headerField->method('getName')->willReturn('key');

		$this->translatorManager->addLanguageModule('module_1')->addLanguageModule('module_2');

		$this->translator->method('translate')->willReturnMap([
			['key', 'module_1', [], ''],
			['key', 'module_2', [], 'translated_value']
		]);

		$result = $this->translatorManager->translate($headerField);

		$this->assertSame('translated_value', $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testTranslateShouldReturnEmptyStringWhenNoTranslationIsFound(): void
	{
		$headerField = $this->createMock(HeaderField::class);
		$headerField->method('shouldSkipTranslation')->willReturn(false);
		$headerField->method('hasSpecificLangModule')->willReturn(false);
		$headerField->method('getName')->willReturn('key');

		$this->translatorManager->addLanguageModule('module_1')->addLanguageModule('module_2');

		$this->translator->method('translate')->willReturn('');

		$result = $this->translatorManager->translate($headerField);

		$this->assertSame('', $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testTranslateShouldHandleFrameworkExceptionGracefully(): void
	{
		$headerField = $this->createMock(HeaderField::class);
		$headerField->method('shouldSkipTranslation')->willReturn(false);
		$headerField->method('hasSpecificLangModule')->willReturn(false);
		$headerField->method('getName')->willReturn('key');

		$this->translatorManager->addLanguageModule('module_1')->addLanguageModule('module_2');

		$this->translator->method('translate')->will($this->throwException(new FrameworkException('error')));

		$result = $this->translatorManager->translate($headerField);

		$this->assertSame('', $result);
	}
}