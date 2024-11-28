<?php

namespace Tests\Unit\Framework\TemplateEngine;

use App\Framework\TemplateEngine\MustacheAdapter;
use Mustache_Engine;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class MustacheAdapterTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testRenderReturnsExpectedOutput()
	{
		$mustacheMock = $this->createMock(Mustache_Engine::class);

		$template = 'Hello, {{name}}!';
		$data = ['name' => 'World'];
		$expectedOutput = 'Hello, World!';

		$mustacheMock->expects($this->once())
			->method('render')
			->with($template, $data)
			->willReturn($expectedOutput);

		$adapter = new MustacheAdapter($mustacheMock);

		$output = $adapter->render($template, $data);
		$this->assertEquals($expectedOutput, $output);
	}
}
