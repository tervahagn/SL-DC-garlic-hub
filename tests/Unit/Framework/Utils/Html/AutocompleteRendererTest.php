<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Utils\Html\AutocompleteField;
use App\Framework\Utils\Html\AutocompleteRenderer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class AutocompleteRendererTest extends TestCase
{
	private AutocompleteField $fieldMock;
	private AutocompleteRenderer $renderer;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->fieldMock = $this->createMock(AutocompleteField::class);
		$this->renderer = new AutocompleteRenderer();
	}

	#[Group('units')]
	public function testRenderGeneratesCorrectHtml(): void
	{
		$this->fieldMock->method('getId')->willReturn('test-field');
		$this->fieldMock->method('getValue')->willReturn('123');
		$this->fieldMock->method('getDataLabel')->willReturn('Test Label');

		$expectedHtml = '<input id="test-field_search" list="test-field_suggestions" value="Test Label" data-id="123" aria-describedby="error_test-field">
		<input type="hidden" id="test-field" name="test-field" value="123" autocomplete="off">
		<datalist id = "test-field_suggestions" ></datalist>';

		$result = $this->renderer->render($this->fieldMock);

		$this->assertSame($expectedHtml, $result);
	}
}
