<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Utils\Html\FieldInterface;
use App\Framework\Utils\Html\PasswordRenderer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class PasswordRendererTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testRenderWithMinimumAttributes(): void
	{
		$fieldMock = $this->createMock(FieldInterface::class);
		$fieldMock->method('getName')->willReturn('password');
		$fieldMock->method('getId')->willReturn('password_1');
		$fieldMock->method('getValue')->willReturn('janzjeheim');
		$fieldMock->method('getValidationRules')->willReturn([]);
		$fieldMock->method('getAttributes')->willReturn([]);

		$renderer = new PasswordRenderer();
		$result = $renderer->render($fieldMock);

		$expected = '<input type="password" name="password" id="password_1" value="janzjeheim" aria-describedby="error_password_1">';
		$this->assertSame($expected, $result);
	}
}
