<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Utils\Html\CsrfTokenRenderer;
use App\Framework\Utils\Html\FieldInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class CsrfTokenRendererTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testRenderWithBasicAttributes(): void
	{
		$fieldMock = $this->createMock(FieldInterface::class);
		$fieldMock->method('getName')->willReturn('csrf_token');
		$fieldMock->method('getId')->willReturn('csrf_token');
		$fieldMock->method('getValue')->willReturn('the_token_in_some_hash');

		$renderer = new CsrfTokenRenderer();
		$result = $renderer->render($fieldMock);

		$expected = '<input type="hidden" name="csrf_token" id="csrf_token" value="the_token_in_some_hash">';
		$this->assertSame($expected, $result);
	}
}
