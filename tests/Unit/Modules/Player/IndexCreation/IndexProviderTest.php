<?php

namespace Tests\Unit\Modules\Player\IndexCreation;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\IndexCreation\IndexCreator;
use App\Modules\Player\IndexCreation\IndexProvider;
use League\Flysystem\FilesystemException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexProviderTest extends TestCase
{
	private readonly Config&MockObject $configMock;
	private readonly IndexCreator&MockObject $indexCreatorMock;
	private readonly PlayerEntity&MockObject $playerEntityMock;

	private IndexProvider $indexProvider;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->configMock       = $this->createMock(Config::class);
		$this->indexCreatorMock = $this->createMock(IndexCreator::class);

		$this->indexProvider = new IndexProvider($this->configMock, $this->indexCreatorMock);

		$this->playerEntityMock = $this->createMock(PlayerEntity::class);
		$this->configMock->method('getConfigValue')
			->willReturnMap([
				['defaults', 'player', 'SmilDirectories', 'var/www/defaults'],
				['tests', 'player', 'SmilDirectories', 'var/www/tests'],
				['simulations', 'player', 'SmilDirectories', 'var/www/simulate']
			]);

	}


	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testHandleForbidden(): void
	{
		$this->indexProvider->handleForbidden();

		$expectedPath = '/var/www/defaults/forbidden.smil';
		$this->assertSame($expectedPath, $this->indexProvider->getFilePath());
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testHandleNew(): void
	{
		$this->indexProvider->handleNew($this->playerEntityMock);

		$expectedPath = '/var/www/defaults/unreleased.smil';
		$this->assertSame($expectedPath, $this->indexProvider->getFilePath());
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testHandleUnreleased(): void
	{
		$this->indexProvider->handleUnreleased();

		$expectedPath = '/var/www/defaults/unreleased.smil';
		$this->assertSame($expectedPath, $this->indexProvider->getFilePath());
	}

	/**
	 * @throws CoreException
	 * @throws FilesystemException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testHandleReleasedWithoutPlaylist(): void
	{
		$this->playerEntityMock->method('getPlaylistId')->willReturn(0);

		$this->indexProvider->handleReleased($this->playerEntityMock);

		$expectedPath = '/var/www/defaults/released.smil';
		$this->assertSame($expectedPath, $this->indexProvider->getFilePath());
	}

	/**
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testHandleReleasedWithPlaylist(): void
	{
		$this->playerEntityMock->method('getPlaylistId')->willReturn(1);
		$this->indexCreatorMock->expects($this->once())->method('createForReleasedPlayer')
			->with($this->playerEntityMock, $this->configMock);

		$this->indexCreatorMock->method('getIndexFilePath')->willReturn('var/www/tests/released.smil');

		$this->indexProvider->handleReleased($this->playerEntityMock);

		$expectedPath = '/var/www/tests/released.smil';
		$this->assertSame($expectedPath, $this->indexProvider->getFilePath());
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testHandleTestSMil(): void
	{
		$this->indexProvider->handleTestSMil();

		$expectedPath = '/var/www/tests/index.smil';
		$this->assertSame($expectedPath, $this->indexProvider->getFilePath());
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testHandleCorrectSMil(): void
	{
		$this->indexProvider->handleCorrectSMil();

		$expectedPath = '/var/www/simulate/without_errors.smil';
		$this->assertSame($expectedPath, $this->indexProvider->getFilePath());
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testHandleCorruptSMil(): void
	{
		$this->indexProvider->handleCorruptSMIL();

		$expectedPath = '/var/www/simulate/broken_index.smil';
		$this->assertSame($expectedPath, $this->indexProvider->getFilePath());
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testHandleCorruptContent(): void
	{
		$this->indexProvider->handleCorruptContent();

		$expectedPath = '/var/www/simulate/unreachable_content.smil';
		$this->assertSame($expectedPath, $this->indexProvider->getFilePath());
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testHandleCorruptPrefetch(): void
	{
		$this->indexProvider->handleCorruptPrefetchContent();

		$expectedPath = '/var/www/simulate/unreachable_prefetch_content.smil';
		$this->assertSame($expectedPath, $this->indexProvider->getFilePath());
	}


}
