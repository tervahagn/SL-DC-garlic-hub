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

use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\NumberField;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class NumberFieldTest extends TestCase
{
	private NumberField $numberField;

	#[Group('units')]
	public function testConstructSetsMinAttribute(): void
	{
		$attributes = [
			'id' => 'logins',
			'type' => FieldType::NUMBER,
			'name' => 'user_logins',
			'min' => 5
		];

		$this->numberField = new NumberField($attributes);

		$rAttributes = $this->numberField->getAttributes();
		$this->assertArrayHasKey('min', $rAttributes);
		$this->assertArrayNotHasKey('max', $rAttributes);
		$this->assertSame('5', $rAttributes['min']);
	}

	#[Group('units')]
	public function testConstructSetsMaxAttribute(): void
	{
		$attributes = [
			'id' => 'logins',
			'type' => FieldType::NUMBER,
			'name' => 'user_logins',
			'max' => 15
		];

		$this->numberField = new NumberField($attributes);

		$rAttributes = $this->numberField->getAttributes();
		$this->assertArrayNotHasKey('min', $rAttributes);
		$this->assertArrayHasKey('max', $rAttributes);
		$this->assertSame('15', $rAttributes['max']);
	}

	#[Group('units')]
	public function testConstructHandlesNoAttributes(): void
	{
		$attributes = [
			'id' => 'logins',
			'type' => FieldType::NUMBER,
			'name' => 'user_logins'
		];

		$this->numberField = new NumberField($attributes);

		$rAttributes = $this->numberField->getAttributes();
		$this->assertArrayNotHasKey('min', $rAttributes);
		$this->assertArrayNotHasKey('max', $rAttributes);
	}

	#[Group('units')]
	public function testConstructHandlesBothAttributes(): void
	{
		$attributes = [
			'id' => 'logins',
			'type' => FieldType::NUMBER,
			'name' => 'user_logins',
			'min' => 5,
			'max' => 15
		];

		$this->numberField = new NumberField($attributes);

		$rAttributes = $this->numberField->getAttributes();
		$this->assertArrayHasKey('min', $rAttributes);
		$this->assertArrayHasKey('max', $rAttributes);

		$this->assertSame('5', $rAttributes['min']);
		$this->assertSame('15', $rAttributes['max']);
	}
}
