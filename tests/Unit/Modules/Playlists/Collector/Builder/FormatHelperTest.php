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


namespace Tests\Unit\Modules\Playlists\Collector\Builder;

use App\Modules\Playlists\Collector\Builder\FormatHelper;
use App\Modules\Playlists\Helper\ExportSmil\items\Base;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class FormatHelperTest extends TestCase
{

	#[Group('units')]
	public function testWrapWithSequence(): void
	{
		$content = "<item>Sample Content</item>";
		$expectedOutput = Base::TABSTOPS_TAG . '<seq repeatCount="indefinite">' . "\n" .
			$content .
			Base::TABSTOPS_TAG . '</seq>' . "\n";

		$result = FormatHelper::wrapWithSequence($content);

		static::assertSame($expectedOutput, $result);
	}

	#[Group('units')]
	public function testWrapWithSequenceHandlesEmptyContent(): void
	{
		$content = "";
		$expectedOutput = Base::TABSTOPS_TAG . '<seq repeatCount="indefinite">' . "\n" .
			$content .
			Base::TABSTOPS_TAG . '</seq>' . "\n";

		$result = FormatHelper::wrapWithSequence($content);

		static::assertSame($expectedOutput, $result);
	}

	#[Group('units')]
	public function testWrapWithSequenceHandlesLargeContent(): void
	{
		$content = str_repeat("LargeContent", 1000);
		$expectedOutput = Base::TABSTOPS_TAG . '<seq repeatCount="indefinite">' . "\n" .
			$content .
			Base::TABSTOPS_TAG . '</seq>' . "\n";

		$result = FormatHelper::wrapWithSequence($content);

		static::assertSame($expectedOutput, $result);
	}

	#
	#[Group('units')]
	public function testFormatMultiZoneItems(): void
	{
		$screenId = 1;
		$items = '<item region="screen">Content</item>';
		$expectedOutput = Base::TABSTOPS_TAG . '<seq id="media1" repeatCount="indefinite">' . "\n" .
			'<item region="screen1">Content</item>' .
			Base::TABSTOPS_TAG . '</seq>' . "\n";

		$result = FormatHelper::formatMultiZoneItems($screenId, $items);

		static::assertSame($expectedOutput, $result);
	}

	#[Group('units')]
	public function testFormatMultiZoneExclusive(): void
	{
		$screenId = 2;
		$exclusive = '<item region="screen">Exclusive Content</item>';
		$expectedOutput = '<item region="screen2">Exclusive Content</item>';

		$result = FormatHelper::formatMultiZoneExclusive($screenId, $exclusive);

		static::assertSame($expectedOutput, $result);
	}

	#[Group('units')]
	public function testFormatMultiZoneExclusiveWithEmptyExclusive(): void
	{
		$screenId = 3;
		$exclusive = '';
		$expectedOutput = '';

		$result = FormatHelper::formatMultiZoneExclusive($screenId, $exclusive);

		static::assertSame($expectedOutput, $result);
	}

}