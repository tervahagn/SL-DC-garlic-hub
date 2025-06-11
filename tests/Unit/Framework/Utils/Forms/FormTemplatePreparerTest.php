<?php

namespace Tests\Unit\Framework\Utils\Forms;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Utils\Forms\FormTemplatePreparer;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;

class FormTemplatePreparerTest extends TestCase
{
	private FormTemplatePreparer $formTemplatePreparer;
	private Translator $translatorMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->translatorMock = $this->createMock(Translator::class);
		$this->formTemplatePreparer = new FormTemplatePreparer($this->translatorMock);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testPrepareUITemplateReturnsCorrectStructure(): void
	{
		$dataSections = [
			'title' => 'Sample Title',
			'additional_css' => ['style.css'],
			'footer_modules' => ['module.js'],
			'template_name' => 'form_template',
			'form_action' => '/submit-form',
			'hidden' => ['hidden_field' => 'value'],
			'visible' => ['field1' => 'value1', 'field2' => 'value2'],
			'save_button_label' => 'Save Changes',
		];

		$expected = [
			'main_layout' => [
				'LANG_PAGE_TITLE' => 'Sample Title',
				'additional_css' => ['style.css'],
				'footer_modules' => ['module.js'],
			],
			'this_layout' => [
				'template' => 'form_template',
				'data' => [
					'LANG_PAGE_HEADER' => 'Sample Title',
					'FORM_ACTION' => '/submit-form',
					'element_hidden' => ['hidden_field' => 'value'],
					'form_element' => ['field1' => 'value1', 'field2' => 'value2'],
					'form_button' => [
						[
							'ELEMENT_BUTTON_TYPE' => 'submit',
							'ELEMENT_BUTTON_NAME' => 'submit',
							'LANG_ELEMENT_BUTTON' => 'Save Changes',
						],
					],
				],
			],
		];

		$result = $this->formTemplatePreparer->prepareUITemplate($dataSections);

		$this->assertSame($expected, $result);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testPrepareUITemplateHandlesEmptyData(): void
	{
		$dataSections = [
			'title' => '',
			'additional_css' => [],
			'footer_modules' => [],
			'template_name' => '',
			'form_action' => '',
			'hidden' => [],
			'visible' => [],
			'save_button_label' => '',
		];

		$expected = [
			'main_layout' => [
				'LANG_PAGE_TITLE' => '',
				'additional_css' => [],
				'footer_modules' => [],
			],
			'this_layout' => [
				'template' => '',
				'data' => [
					'LANG_PAGE_HEADER' => '',
					'FORM_ACTION' => '',
					'element_hidden' => [],
					'form_element' => [],
					'form_button' => [
						[
							'ELEMENT_BUTTON_TYPE' => 'submit',
							'ELEMENT_BUTTON_NAME' => 'submit',
							'LANG_ELEMENT_BUTTON' => '',
						],
					],
				],
			],
		];

		$result = $this->formTemplatePreparer->prepareUITemplate($dataSections);

		$this->assertSame($expected, $result);
	}
}