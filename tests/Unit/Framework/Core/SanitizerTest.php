<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


namespace Tests\Unit\Framework\Core;

use App\Framework\Core\Sanitizer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class SanitizerTest extends TestCase
{
	private Sanitizer $sanitizer;

	protected function setUp(): void
	{
		$this->sanitizer = new Sanitizer('<b><i>');
	}

	#[Group('units')]
	public function testStringSanitization(): void
	{
		$this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', $this->sanitizer->string('<script>alert(1)</script>'));
		$this->assertSame('Hello &quot;World&quot;', $this->sanitizer->string('Hello "World"'));
	}

	#[Group('units')]
	public function testHtmlSanitization(): void
	{
		$this->assertSame('<b>bold</b>', $this->sanitizer->html('<b>bold</b>'));
		$this->assertSame('alert()', $this->sanitizer->html('<script>alert()</script>'));
	}

	#[Group('units')]
	public function testIntSanitization(): void
	{
		$this->assertSame(123, $this->sanitizer->int('123abc'));
		$this->assertSame(0, $this->sanitizer->int('abc'));
	}

	#[Group('units')]
	public function testFloatSanitization(): void
	{
		$this->assertSame(12.34, $this->sanitizer->float('12.34abc'));
		$this->assertSame(0.0, $this->sanitizer->float('abc'));
	}

	#[Group('units')]
	public function testBoolSanitization(): void
	{
		$this->assertTrue($this->sanitizer->bool('1'));
		$this->assertFalse($this->sanitizer->bool(''));
		$this->assertTrue($this->sanitizer->bool('true'));
	}

	#[Group('units')]
	public function testStringArraySanitization(): void
	{
		$this->assertSame(
			['&lt;script&gt;alert(1)&lt;/script&gt;', 'Hello &quot;World&quot;'],
			$this->sanitizer->stringArray(['<script>alert(1)</script>', 'Hello "World"'])
		);

		$this->assertSame(
			['foo', 'bar', 'baz'],
			$this->sanitizer->stringArray(['foo', 'bar', 'baz'])
		);

		$this->assertSame(
			['&lt;div&gt;', '&amp;', '&lt;br&gt;'],
			$this->sanitizer->stringArray(['<div>', '&', '<br>'])
		);

		$this->assertSame(
			[],
			$this->sanitizer->stringArray([])
		);
	}

	#[Group('units')]
	public function testIntArraySanitization(): void
	{
		$this->assertSame(
			[123, 456, 789],
			$this->sanitizer->intArray(['123', '456', '789'])
		);

		$this->assertSame(
		[123, 0, 789],
			$this->sanitizer->intArray(['123', 'abc', '789'])
		);

		$this->assertSame(
		[0, 1, 999],
			$this->sanitizer->intArray(['0', '1', '999'])
		);

		$this->assertSame(
		[0],
			$this->sanitizer->intArray(['abc'])
		);

		$this->assertSame(
			[],
			$this->sanitizer->intArray([])
		);
	}

	#[Group('units')]
	public function testFloatArraySanitization(): void
	{
		$this->assertSame(
			[12.34, 56.78, 90.12],
			$this->sanitizer->floatArray(['12.34', '56.78', '90.12'])
		);

		$this->assertSame(
			[12.34, 0.0, 0.789],
			$this->sanitizer->floatArray(['12.34', 'not a number', '.789'])
		);

		$this->assertSame(
			[0.0, 1.0, 999.99],
			$this->sanitizer->floatArray(['0', '1', '999.99'])
		);

		$this->assertSame(
			[0.0],
			$this->sanitizer->floatArray(['invalid'])
		);

		$this->assertSame(
			[],
			$this->sanitizer->floatArray([])
		);
	}

	#[Group('units')]
	public function testJsonArraySanitization(): void
	{
		$this->assertSame(
			['key' => 'value', 'number' => 123, 'bool' => true],
			$this->sanitizer->jsonArray('{"key": "value", "number": 123, "bool": true}')
		);

		$this->assertSame(
			[['id' => 1], ['id' => 2], ['id' => 3]],
			$this->sanitizer->jsonArray('[{"id": 1}, {"id": 2}, {"id": 3}]')
		);

		$this->assertSame([], $this->sanitizer->jsonArray('invalid json'));

		$this->assertSame([], $this->sanitizer->jsonArray('null'));

		$this->assertSame([], $this->sanitizer->jsonArray('{"incomplete":'));

		$this->assertSame([], $this->sanitizer->jsonArray(''));
	}
}
