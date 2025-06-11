<?php

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
		$this->playerEntityMock = $this->createMock(PlayerEntity::class);
		$this->preparerFactory = new PreparerFactory();
	}

	#[Group('units')]
	public function testCreateReturnsMetaPreparer(): void
	{
		$result = $this->preparerFactory->create(IndexSections::META, $this->playerEntityMock);
		$this->assertInstanceOf(MetaPreparer::class, $result);
	}

	#[Group('units')]
	public function testCreateReturnsSubscriptionPreparer(): void
	{
		$result = $this->preparerFactory->create(IndexSections::SUBSCRIPTIONS, $this->playerEntityMock);
		$this->assertInstanceOf(SubscriptionPreparer::class, $result);
	}

	#[Group('units')]
	public function testCreateReturnsLayoutPreparer(): void
	{
		$result = $this->preparerFactory->create(IndexSections::LAYOUT, $this->playerEntityMock);
		$this->assertInstanceOf(LayoutPreparer::class, $result);
	}

	#[Group('units')]
	public function testCreateReturnsScreenTimesPreparer(): void
	{
		$result = $this->preparerFactory->create(IndexSections::STANDBY_TIMES, $this->playerEntityMock);
		$this->assertInstanceOf(ScreenTimesPreparer::class, $result);
	}

	#[Group('units')]
	public function testCreateReturnsPlaylistPreparer(): void
	{
		$result = $this->preparerFactory->create(IndexSections::PLAYLIST, $this->playerEntityMock);
		$this->assertInstanceOf(PlaylistPreparer::class, $result);
	}
}
