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

use App\Framework\Utils\Html\HiddenField;
use App\Framework\Utils\Html\HiddenRenderer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HiddenRendererTest extends TestCase
{
	private HiddenRenderer $hiddenRenderer;

	private HiddenField&MockObject $fieldMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
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
		static::assertSame($expectedHtml, $this->hiddenRenderer->render($this->fieldMock));
	}


}
