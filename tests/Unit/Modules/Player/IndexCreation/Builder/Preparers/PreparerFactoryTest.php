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

namespace Tests\Unit\Modules\Player\IndexCreation\Builder\Preparers;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\Enums\IndexSections;
use App\Modules\Player\IndexCreation\Builder\Preparers\LayoutPreparer;
use App\Modules\Player\IndexCreation\Builder\Preparers\MetaPreparer;
use App\Modules\Player\IndexCreation\Builder\Preparers\PlaylistPreparer;
use App\Modules\Player\IndexCreation\Builder\Preparers\PreparerFactory;
use App\Modules\Player\IndexCreation\Builder\Preparers\ScreenTimesPreparer;
use App\Modules\Player\IndexCreation\Builder\Preparers\SubscriptionPreparer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PreparerFactoryTest extends TestCase
{
	private PlayerEntity&MockObject $playerEntityMock;
	private PreparerFactory $preparerFactory;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->playerEntityMock = $this->createMock(PlayerEntity::class);
		$this->preparerFactory = new PreparerFactory();
	}

	/**
	 * @throws \Exception
	 */
	#[Group('units')]
	public function testCreateReturnsMetaPreparer(): void
	{
		$result = $this->preparerFactory->create(IndexSections::META, $this->playerEntityMock);
		static::assertInstanceOf(MetaPreparer::class, $result);
	}

	/**
	 * @throws \Exception
	 */
	#[Group('units')]
	public function testCreateReturnsSubscriptionPreparer(): void
	{
		$result = $this->preparerFactory->create(IndexSections::SUBSCRIPTIONS, $this->playerEntityMock);
		static::assertInstanceOf(SubscriptionPreparer::class, $result);
	}

	/**
	 * @throws \Exception
	 */
	#[Group('units')]
	public function testCreateReturnsLayoutPreparer(): void
	{
		$result = $this->preparerFactory->create(IndexSections::LAYOUT, $this->playerEntityMock);
		static::assertInstanceOf(LayoutPreparer::class, $result);
	}

	/**
	 * @throws \Exception
	 */
	#[Group('units')]
	public function testCreateReturnsScreenTimesPreparer(): void
	{
		$result = $this->preparerFactory->create(IndexSections::STANDBY_TIMES, $this->playerEntityMock);
		static::assertInstanceOf(ScreenTimesPreparer::class, $result);
	}

	/**
	 * @throws \Exception
	 */
	#[Group('units')]
	public function testCreateReturnsPlaylistPreparer(): void
	{
		$result = $this->preparerFactory->create(IndexSections::PLAYLIST, $this->playerEntityMock);
		static::assertInstanceOf(PlaylistPreparer::class, $result);
	}
}
