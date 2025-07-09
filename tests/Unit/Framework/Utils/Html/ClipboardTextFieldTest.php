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

use App\Framework\Utils\Html\ClipboardTextField;
use App\Framework\Utils\Html\FieldType;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ClipboardTextFieldTest extends TestCase
{

	private ClipboardTextField $clipboardTextField;

	/**
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$attributes = [
			'id' => 'clipboard_text',
			'type' => FieldType::CLIPBOARD_TEXT,
		];	$this->clipboardTextField = new ClipboardTextField($attributes);
	}


    #[Group('units')]
	public function testGetDeleteTitleWithDefaultValue(): void
	{
		static::assertSame('', $this->clipboardTextField->getDeleteTitle());
	}

    #[Group('units')]
	public function testGetDeleteTitleAfterSettingValue(): void
	{
		$expectedTitle = 'Delete Item';
		$this->clipboardTextField->setDeleteTitle($expectedTitle);
		static::assertSame($expectedTitle, $this->clipboardTextField->getDeleteTitle());
	}

	#[Group('units')]
	public function testGetRefreshTitleWithDefaultValue(): void
	{
		static::assertSame('', $this->clipboardTextField->getRefreshTitle());
	}

	#[Group('units')]
	public function testGetRefreshTitleAfterSettingValue(): void
	{
		$expectedTitle = 'Refresh Page';
		$this->clipboardTextField->setRefreshTitle($expectedTitle);
		static::assertSame($expectedTitle, $this->clipboardTextField->getRefreshTitle());
	}
}
