<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Utils\Html\DropdownField;
use App\Framework\Utils\Html\DropdownRenderer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class DropdownRendererTest extends TestCase
{
	private DropdownRenderer $renderer;
	private DropdownField $dropdownFieldMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->dropdownFieldMock = $this->createMock(DropdownField::class);
		$this->renderer = new DropdownRenderer();
	}

	#[Group('units')]
	public function testRenderGeneratesValidHtml(): void
	{
		$this->dropdownFieldMock->method('getId')->willReturn('test-id');
		$this->dropdownFieldMock->method('getName')->willReturn('test-name');
		$this->dropdownFieldMock->method('getOptions')->willReturn(['1' => 'Option 1', '2' => 'Option 2']);
		$this->dropdownFieldMock->method('getValue')->willReturn(null);

		$html = $this->renderer->render($this->dropdownFieldMock);

		$expectedHtml = '<select id="test-id" name= "test-name" aria-describedby="error_test-id">'
			. '<option value="">-</option>'
			. '<option value="1">Option 1</option>'
			. '<option value="2">Option 2</option>'
			. '</select>';

		$this->assertSame($expectedHtml, $html);
	}

	#[Group('units')]
	public function testRenderWithSelectedValue(): void
	{
		$this->dropdownFieldMock->method('getId')->willReturn('test-id');
		$this->dropdownFieldMock->method('getName')->willReturn('test-name');
		$this->dropdownFieldMock->method('getOptions')->willReturn(['1' => 'Option 1', '2' => 'Option 2']);
		$this->dropdownFieldMock->method('getValue')->willReturn('2');

		$html = $this->renderer->render($this->dropdownFieldMock);

		$expectedHtml = '<select id="test-id" name= "test-name" aria-describedby="error_test-id">'
			. '<option value="">-</option>'
			. '<option value="1">Option 1</option>'
			. '<option value="2" selected>Option 2</option>'
			. '</select>';

		$this->assertSame($expectedHtml, $html);
	}

	#[Group('units')]
	public function testRenderWithEmptyOptions(): void
	{
		$this->dropdownFieldMock->method('getId')->willReturn('test-id');
		$this->dropdownFieldMock->method('getName')->willReturn('test-name');
		$this->dropdownFieldMock->method('getOptions')->willReturn([]);
		$this->dropdownFieldMock->method('getValue')->willReturn(null);

		$html = $this->renderer->render($this->dropdownFieldMock);

		$expectedHtml = '<select id="test-id" name= "test-name" aria-describedby="error_test-id">'
			. '<option value="">-</option>'
			. '</select>';

		$this->assertSame($expectedHtml, $html);
	}
}
