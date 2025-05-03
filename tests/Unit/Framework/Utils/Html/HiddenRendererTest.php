<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Utils\Html\FieldInterface;
use App\Framework\Utils\Html\HiddenField;
use App\Framework\Utils\Html\HiddenRenderer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HiddenRendererTest extends TestCase
{
	private HiddenRenderer $hiddenRenderer;

	private HiddenField $fieldMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->fieldMock = $this->createMock(HiddenField::class);
		$this->hiddenRenderer = new HiddenRenderer();
	}

	#[Group('units')]
	public function testRenderGeneratesCorrectHtml(): void
	{

		$this->fieldMock->method('getName')->willReturn('testField');
		$this->fieldMock->method('getId')->willReturn('hiddenField');
		$this->fieldMock->method('getValue')->willReturn('hiddenValue');
		$this->fieldMock->method('getTitle')->willReturn('');
		$this->fieldMock->method('getLabel')->willReturn('');

		$expectedHtml = '<input type="hidden" name="testField" id="hiddenField" value="hiddenValue">';
		$this->assertSame($expectedHtml, $this->hiddenRenderer->render($this->fieldMock));
	}


}
