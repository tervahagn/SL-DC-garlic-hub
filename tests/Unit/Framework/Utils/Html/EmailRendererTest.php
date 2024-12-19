<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Utils\Html\EmailRenderer;
use App\Framework\Utils\Html\FieldInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class EmailRendererTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testRenderWithMinimumAttributes(): void
	{
		$fieldMock = $this->createMock(FieldInterface::class);
		$fieldMock->method('getName')->willReturn('email_1');
		$fieldMock->method('getId')->willReturn('email_1');
		$fieldMock->method('getValue')->willReturn('test@test.kl');
		$fieldMock->method('getValidationRules')->willReturn([]);
		$fieldMock->method('getAttributes')->willReturn([]);

		$renderer = new EmailRenderer();
		$result = $renderer->render($fieldMock);

		$expected = '<input type="email" name="email_1" id="email_1" value="test@test.kl" aria-describedby="error_email_1">';
		$this->assertSame($expected, $result);
	}
}
