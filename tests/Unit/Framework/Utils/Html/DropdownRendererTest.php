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

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Utils\Html\DropdownField;
use App\Framework\Utils\Html\DropdownRenderer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DropdownRendererTest extends TestCase
{
	private DropdownRenderer $renderer;
	private DropdownField&MockObject $dropdownFieldMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
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
		$this->dropdownFieldMock->method('isOptionsZero')->willReturn(true);

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
		$this->dropdownFieldMock->method('isOptionsZero')->willReturn(false);

		$html = $this->renderer->render($this->dropdownFieldMock);

		$expectedHtml = '<select id="test-id" name= "test-name" aria-describedby="error_test-id">'
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
		$this->dropdownFieldMock->method('isOptionsZero')->willReturn(true);

		$html = $this->renderer->render($this->dropdownFieldMock);

		$expectedHtml = '<select id="test-id" name= "test-name" aria-describedby="error_test-id">'
			. '<option value="">-</option>'
			. '</select>';

		$this->assertSame($expectedHtml, $html);
	}
}
