<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Utils\Html\FieldInterface;
use App\Framework\Utils\Html\NumberField;
use App\Framework\Utils\Html\NumberRenderer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class NumberRendererTest extends TestCase
{
	private NumberRenderer $numberRenderer;
	private NumberField $fieldMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->fieldMock = $this->createMock(NumberField::class);
		$this->numberRenderer = new NumberRenderer();
	}

	#[Group('units')]
	public function testRenderCorrectHtml(): void
	{
		$this->fieldMock->method('getId')->willReturn('test_id');
		$this->fieldMock->method('getName')->willReturn('test_name');
		$this->fieldMock->method('getValue')->willReturn('123');
		$this->fieldMock->method('getAttributes')->willReturn(['class' => 'test-class']);
		$this->fieldMock->method('getValidationRules')->willReturn([]);
		$this->fieldMock->method('getTitle')->willReturn('Test Title');
		$this->fieldMock->method('getLabel')->willReturn('A Label');

		$expectedHtml = '<input type="number" name="test_name" id="test_id" value="123" title="Test Title" label="A Label" class="test-class" aria-describedby="error_test_id">';

		$actualHtml = $this->numberRenderer->render($this->fieldMock);

		$this->assertSame($expectedHtml, $actualHtml);
	}
}
