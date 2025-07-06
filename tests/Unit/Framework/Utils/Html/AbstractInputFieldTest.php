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

use App\Framework\Utils\Html\AbstractInputField;
use App\Framework\Utils\Html\FieldType;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ConcreteInputField extends AbstractInputField
{

}

class AbstractInputFieldTest extends TestCase
{

	#[Group('units')]
	public function testConstructorWithValidAttributes(): void
	{
		$field = new ConcreteInputField([
			'id' => 'custom_id',
			'type' => FieldType::TEXT,
			'name' => 'custom_name',
			'title' => 'Custom Title',
			'label' => 'Custom Label',
			'value' => 'Custom Value',
			'default_value' => 'Default Value',
			'attributes' => ['attr1' => 'value1'],
			'rules' => ['required' => true]
		]);

		static::assertSame('custom_id', $field->getId());
		static::assertSame(FieldType::TEXT, $field->getType());
		static::assertSame('custom_name', $field->getName());
		static::assertSame('Custom Title', $field->getTitle());
		static::assertSame('Custom Label', $field->getLabel());
		static::assertSame('Custom Value', $field->getValue());
		static::assertSame(['attr1' => 'value1'], $field->getAttributes());
		static::assertSame(['required' => true], $field->getValidationRules());
	}


}
