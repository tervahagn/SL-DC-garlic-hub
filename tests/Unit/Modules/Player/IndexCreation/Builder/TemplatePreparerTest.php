<?php

namespace Tests\Unit\Modules\Player\IndexCreation\Builder;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\Enums\IndexSections;
use App\Modules\Player\Enums\TemplateIndexFiles;
use App\Modules\Player\IndexCreation\Builder\Preparers\LayoutPreparer;
use App\Modules\Player\IndexCreation\Builder\Preparers\MetaPreparer;
use App\Modules\Player\IndexCreation\Builder\Preparers\PlaylistPreparer;
use App\Modules\Player\IndexCreation\Builder\Preparers\PreparerFactory;
use App\Modules\Player\IndexCreation\Builder\Preparers\ScreenTimesPreparer;
use App\Modules\Player\IndexCreation\Builder\Preparers\SubscriptionPreparer;
use App\Modules\Player\IndexCreation\Builder\TemplatePreparer;
use App\Modules\Playlists\Collector\Contracts\PlaylistStructureInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class TemplatePreparerTest extends TestCase
{
	private PlayerEntity $playerEntityMock;
	private PlaylistStructureInterface $playlistStructureMock;
	private readonly PreparerFactory $preparerFactoryMock;
	private TemplatePreparer $templatePreparer;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->playerEntityMock = $this->createMock(PlayerEntity::class);
		$this->playlistStructureMock = $this->createMock(PlaylistStructureInterface::class);
		$this->preparerFactoryMock = $this->createMock(PreparerFactory::class);

		$this->templatePreparer = new TemplatePreparer($this->preparerFactoryMock);
		$this->templatePreparer->setPlayerEntity($this->playerEntityMock);
		$this->templatePreparer->setPlaylistStructure($this->playlistStructureMock);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testPrepareForGarlic(): void
	{
		$metaMock         = $this->createMock(MetaPreparer::class);
		$subscriptionMock = $this->createMock(SubscriptionPreparer::class);
		$layoutMock       = $this->createMock(LayoutPreparer::class);
		$screenTimesMock  = $this->createMock(ScreenTimesPreparer::class);
		$playlistMock     = $this->createMock(PlaylistPreparer::class);

		$this->preparerFactoryMock->expects($this->any())
			->method('create')
			->willReturnMap([
				[IndexSections::META, $this->playerEntityMock, $metaMock],
				[IndexSections::SUBSCRIPTIONS, $this->playerEntityMock, $subscriptionMock],
				[IndexSections::LAYOUT, $this->playerEntityMock, $layoutMock],
				[IndexSections::STANDBY_TIMES, $this->playerEntityMock, $screenTimesMock],
				[IndexSections::PLAYLIST, $this->playerEntityMock, $playlistMock],
			]);
		$metaMock->expects($this->once())->method('prepare')->willReturn(['meta']);
		$subscriptionMock->expects($this->once())->method('prepare')->willReturn(['subscriptions']);
		$layoutMock->expects($this->once())->method('prepare')->willReturn(['layout']);
		$screenTimesMock->expects($this->once())->method('prepare')->willReturn(['standby']);
		$playlistMock->expects($this->once())->method('setPlaylistStructure')
			->with($this->playlistStructureMock)
			->willReturnSelf();
		$playlistMock->expects($this->once())->method('prepare')->willReturn(['playlist']);

		$this->templatePreparer->prepare(TemplateIndexFiles::GARLIC);

		$templateData = $this->templatePreparer->getTemplateData();
		$this->assertEquals([
			'meta' => ['meta'],
			'subscriptions' => ['subscriptions'],
			'layout' => ['layout'],
			'standby_times' => ['standby'],
			'playlist' => ['playlist'],
		], $templateData);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testPrepareForIAdea(): void
	{
		$metaMock         = $this->createMock(MetaPreparer::class);
		$subscriptionMock = $this->createMock(SubscriptionPreparer::class);
		$layoutMock       = $this->createMock(LayoutPreparer::class);
		$screenTimesMock  = $this->createMock(ScreenTimesPreparer::class);
		$playlistMock     = $this->createMock(PlaylistPreparer::class);

		$this->preparerFactoryMock->expects($this->any())
			->method('create')
			->willReturnMap([
				[IndexSections::META, $this->playerEntityMock, $metaMock],
				[IndexSections::SUBSCRIPTIONS, $this->playerEntityMock, $subscriptionMock],
				[IndexSections::LAYOUT, $this->playerEntityMock, $layoutMock],
				[IndexSections::STANDBY_TIMES, $this->playerEntityMock, $screenTimesMock],
				[IndexSections::PLAYLIST, $this->playerEntityMock, $playlistMock],
			]);
		$metaMock->expects($this->once())->method('prepare')->willReturn(['meta']);
		$subscriptionMock->expects($this->once())->method('prepare')->willReturn(['subscriptions']);
		$layoutMock->expects($this->once())->method('prepare')->willReturn(['layout']);
		$screenTimesMock->expects($this->once())->method('prepare')->willReturn(['standby']);
		$playlistMock->expects($this->once())->method('setPlaylistStructure')
			->with($this->playlistStructureMock)
			->willReturnSelf();
		$playlistMock->expects($this->once())->method('prepare')->willReturn(['playlist']);

		$this->templatePreparer->prepare(TemplateIndexFiles::XMP2XXX);

		$templateData = $this->templatePreparer->getTemplateData();
		$this->assertEquals([
			'meta' => ['meta'],
			'subscriptions' => ['subscriptions'],
			'layout' => ['layout'],
			'standby_times' => ['standby'],
			'playlist' => ['playlist'],
		], $templateData);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testPrepareForCompatible(): void
	{
		$metaMock         = $this->createMock(MetaPreparer::class);
		$layoutMock       = $this->createMock(LayoutPreparer::class);
		$playlistMock     = $this->createMock(PlaylistPreparer::class);

		$this->preparerFactoryMock->expects($this->any())
			->method('create')
			->willReturnMap([
				[IndexSections::META, $this->playerEntityMock, $metaMock],
				[IndexSections::LAYOUT, $this->playerEntityMock, $layoutMock],
				[IndexSections::PLAYLIST, $this->playerEntityMock, $playlistMock],
			]);
		$metaMock->expects($this->once())->method('prepare')->willReturn(['meta']);
		$layoutMock->expects($this->once())->method('prepare')->willReturn(['layout']);
		$playlistMock->expects($this->once())->method('setPlaylistStructure')
			->with($this->playlistStructureMock)
			->willReturnSelf();
		$playlistMock->expects($this->once())->method('prepare')->willReturn(['playlist']);

		$this->templatePreparer->prepare(TemplateIndexFiles::SIMPLE);

		$templateData = $this->templatePreparer->getTemplateData();
		$this->assertEquals([
			'meta' => ['meta'],
			'layout' => ['layout'],
			'playlist' => ['playlist'],
		], $templateData);
	}


}
