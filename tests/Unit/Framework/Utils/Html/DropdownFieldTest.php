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
use App\Framework\Utils\Html\FieldType;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class DropdownFieldTest extends TestCase
{
	private DropdownField $dropdownField;

	protected function setUp(): void
	{
		parent::setUp();
		$attributes = [
			'id' => 'dropdown1',
			'type' => FieldType::DROPDOWN,
			'name' => 'dropdown_field',
			'options' => ['Option1' => [], 'Option2' => [], 'Option3' => []]
		];

		$this->dropdownField = new DropdownField($attributes);
	}

	#[Group('units')]
	public function testGetOptionsReturnsCorrectOptions(): void
	{
		$this->assertSame(['Option1' => [], 'Option2' => [], 'Option3' => []], $this->dropdownField->getOptions());
	}
}
