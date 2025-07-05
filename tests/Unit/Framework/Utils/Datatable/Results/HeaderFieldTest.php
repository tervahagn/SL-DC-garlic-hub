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

namespace Tests\Unit\Framework\Utils\Datatable\Results;

use App\Framework\Utils\Datatable\Results\HeaderField;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class HeaderFieldTest extends TestCase
{
	private HeaderField $headerField;

	protected function setUp(): void
	{
		parent::setUp();
		$this->headerField = new HeaderField();
	}

	#[Group('units')]
	public function testIsSortableInitiallyFalse(): void
	{
		$this->assertFalse($this->headerField->isSortable());
	}

	#[Group('units')]
	public function testSortableSetterAndGetter(): void
	{
		$this->headerField->sortable(true);
		$this->assertTrue($this->headerField->isSortable());

		$this->headerField->sortable(false);
		$this->assertFalse($this->headerField->isSortable());
	}

	#[Group('units')]
	public function testShouldSkipTranslationInitiallyFalse(): void
	{
		$this->assertFalse($this->headerField->shouldSkipTranslation());
	}

	#[Group('units')]
	public function testSkipTranslationSetterAndGetter(): void
	{
		$this->headerField->skipTranslation(true);
		$this->assertTrue($this->headerField->shouldSkipTranslation());

		$this->headerField->skipTranslation(false);
		$this->assertFalse($this->headerField->shouldSkipTranslation());
	}

	#[Group('units')]
	public function testHasSpecificLangModuleInitiallyFalse(): void
	{
		$this->assertFalse($this->headerField->hasSpecificLangModule());
	}

	#[Group('units')]
	public function testUseSpecificLangModuleSetterAndGetter(): void
	{
		$this->headerField->useSpecificLangModule('test_module');
		$this->assertTrue($this->headerField->hasSpecificLangModule());
		$this->assertSame('test_module', $this->headerField->getSpecificLanguageModule());
	}
}
