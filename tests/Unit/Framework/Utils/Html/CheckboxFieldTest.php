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

use App\Framework\Utils\Html\CheckboxField;
use App\Framework\Utils\Html\FieldType;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class CheckboxFieldTest extends TestCase
{
	private CheckboxField $checkboxField;

	protected function setUp(): void
	{
		parent::setUp();
		$this->checkboxField = new CheckboxField(['id' => 'id', 'type' => FieldType::CHECKBOX]);
	}

	#[Group('units')]
	public function testSetCheckedTrue(): void
	{
		$this->checkboxField->setChecked(true);
		static::assertTrue($this->checkboxField->isChecked());
	}

	#[Group('units')]
	public function testSetCheckedFalse(): void
	{
		$this->checkboxField->setChecked(false);
		static::assertFalse($this->checkboxField->isChecked());
	}
}
