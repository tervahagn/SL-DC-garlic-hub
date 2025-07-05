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

namespace Tests\Unit\Modules\Playlists\Helper\ExportSmil\items;

use App\Framework\Core\Config\Config;
use App\Modules\Playlists\Helper\ExportSmil\items\Base;
use App\Modules\Playlists\Helper\ExportSmil\items\SeqContainer;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Conditional;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Properties;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Trigger;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SeqContainerTest extends TestCase
{
	private Config&MockObject $configMock;
	private Trigger&MockObject $beginMock;
	private Trigger&MockObject $endMock;
	private Conditional&MockObject $conditionalMock;
	private Properties $propertiesMock;
	private SeqContainer $seqContainer;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		parent::setUp();
		$this->configMock = $this->createMock(Config::class);
		$this->beginMock       = $this->createMock(Trigger::class);
		$this->endMock         = $this->createMock(Trigger::class);
		$this->conditionalMock = $this->createMock(Conditional::class);
		$this->propertiesMock = $this->createMock(Properties::class);
	}


	#[Group('units')]
	public function testGetSmilElementTag(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(false);

		$item = ['item_id' => 1, 'item_name' => 'foo', 'file_resource' => '12345'];

		$this->seqContainer = new SeqContainer(
			$this->configMock,
			$item,
			$this->propertiesMock,
			$this->beginMock,
			$this->endMock,
			$this->conditionalMock
		);

		$expected = Base::TABSTOPS_TAG . '<seq xml:id="1" title="foo" >' . "\n" .
			Base::TABSTOPS_PARAMETER . '{ITEMS_12345}' . "\n" .
			Base::TABSTOPS_TAG . '</seq>' . "\n";

		static::assertSame($expected, $this->seqContainer->getSmilElementTag());
	}

	#[Group('units')]
	public function testGetSmilElementTagWithTriggers(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(true);

		$this->seqContainer = new SeqContainer(
		$this->configMock,
			[],
			$this->propertiesMock,
			$this->beginMock,
			$this->endMock,
			$this->conditionalMock
		);

		static::assertSame('', $this->seqContainer->getSmilElementTag());
	}

	#[Group('units')]
	public function testGetElementLinkWithScheduledStartDate(): void
	{
		$item = ['external_link' => 'example.com'];

		$this->seqContainer = new SeqContainer(
			$this->configMock,
			$item,
			$this->propertiesMock,
			$this->beginMock,
			$this->endMock,
			$this->conditionalMock
		);

		$expected = Base::TABSTOPS_TAG . '{ITEMS_0#example.com}' . "\n";

		static::assertSame($expected, $this->seqContainer->getElementLink());
	}

	#[Group('units')]
	public function testGetPrefetchTag(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(false);

		$item = ['file_resource' => '12345'];

		$this->seqContainer = new SeqContainer(
			$this->configMock,
			$item,
			$this->propertiesMock,
			$this->beginMock,
			$this->endMock,
			$this->conditionalMock
		);

		$expected = Base::TABSTOPS_TAG . '{PREFETCH_12345}' . "\n";

		static::assertSame($expected, $this->seqContainer->getPrefetchTag());
	}
}
