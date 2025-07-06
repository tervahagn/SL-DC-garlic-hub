<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);

namespace Tests\Unit\Framework\TemplateEngine;

use App\Framework\TemplateEngine\MustacheAdapter;
use Mustache\Engine;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class MustacheAdapterTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testRenderReturnsExpectedOutput(): void
	{
		$mustacheMock = $this->createMock(Engine::class);

		$template = 'Hello, {{name}}!';
		$data = ['name' => 'World'];
		$expectedOutput = 'Hello, World!';

		$mustacheMock->expects($this->once())
			->method('render')
			->with($template, $data)
			->willReturn($expectedOutput);

		$adapter = new MustacheAdapter($mustacheMock);

		$output = $adapter->render($template, $data);
		static::assertEquals($expectedOutput, $output);
	}
}
