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


namespace Tests\Unit\Framework\Utils\Datatable\Results;

use App\Framework\Utils\Datatable\Results\Builder;
use App\Framework\Utils\Datatable\Results\HeaderField;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
	private Builder $builder;

	protected function setUp(): void
	{
		$this->builder = new Builder();
	}

	#[Group('units')]
	public function testGetHeaderFieldsInitiallyEmpty(): void
	{
		$this->assertSame([], $this->builder->getHeaderFields());
	}

	#[Group('units')]
	public function testCreateFieldAddsHeaderField(): void
	{
		$this->builder->createField('test_field', true);

		$fields = $this->builder->getHeaderFields();
		$this->assertCount(1, $fields);
		$this->assertInstanceOf(HeaderField::class, $fields[0]);
		$this->assertSame('test_field', $fields[0]->getName());
		$this->assertTrue($fields[0]->isSortable());
	}

	#[Group('units')]
	public function testMultipleCreateFieldCalls(): void
	{
		$this->builder->createField('field1', false);
		$this->builder->createField('field2', true);

		$fields = $this->builder->getHeaderFields();
		$this->assertCount(2, $fields);
		$this->assertSame('field1', $fields[0]->getName());
		$this->assertFalse($fields[0]->isSortable());
		$this->assertSame('field2', $fields[1]->getName());
		$this->assertTrue($fields[1]->isSortable());
	}
}