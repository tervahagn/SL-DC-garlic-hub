<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

use App\Framework\Utils\Html\AutocompleteField;
use App\Framework\Utils\Html\AutocompleteRenderer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AutocompleteRendererTest extends TestCase
{
	private AutocompleteField&MockObject $fieldMock;
	private AutocompleteRenderer $renderer;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->fieldMock = $this->createMock(AutocompleteField::class);
		$this->renderer = new AutocompleteRenderer();
	}

	#[Group('units')]
	public function testRenderGeneratesCorrectHtml(): void
	{
		$this->fieldMock->method('getId')->willReturn('test-field');
		$this->fieldMock->method('getValue')->willReturn('123');
		$this->fieldMock->method('getDataLabel')->willReturn('Test Label');

		$expectedHtml = '<input id="test-field_search" list="test-field_suggestions" value="Test Label" data-id="123" aria-describedby="error_test-field">
		<input type="hidden" id="test-field" name="test-field" value="123" autocomplete="off">
		<datalist id = "test-field_suggestions" ></datalist>';

		$result = $this->renderer->render($this->fieldMock);

		static::assertSame($expectedHtml, $result);
	}
}
