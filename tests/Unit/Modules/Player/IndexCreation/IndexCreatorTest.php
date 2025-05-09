<?php

namespace Tests\Unit\Modules\Player\IndexCreation;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\TemplateEngine\AdapterInterface;
use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\Enums\TemplateIndexFiles;
use App\Modules\Player\IndexCreation\Builder\TemplatePreparer;
use App\Modules\Player\IndexCreation\IndexCreator;
use App\Modules\Player\IndexCreation\IndexFile;
use App\Modules\Player\IndexCreation\IndexTemplateSelector;
use App\Modules\Playlists\Collector\Builder\PlaylistBuilderFactory;
use App\Modules\Playlists\Collector\Contracts\PlaylistBuilderInterface;
use App\Modules\Playlists\Collector\Contracts\PlaylistStructureInterface;
use League\Flysystem\FilesystemException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class IndexCreatorTest extends TestCase
{
	private readonly PlaylistBuilderFactory $playlistBuilderFactoryMock;
	private readonly IndexTemplateSelector $templateSelectorMock;
	private readonly IndexFile $indexFileMock;
	private readonly TemplatePreparer $templatePreparerMock;
	private readonly AdapterInterface $templateServiceMock;
	private readonly IndexCreator $indexCreator;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->playlistBuilderFactoryMock = $this->createMock(PlaylistBuilderFactory::class);
		$this->templateSelectorMock       = $this->createMock(IndexTemplateSelector::class);
		$this->indexFileMock              = $this->createMock(IndexFile::class);
		$this->templatePreparerMock       = $this->createMock(TemplatePreparer::class);
		$this->templateServiceMock        = $this->createMock(AdapterInterface::class);
		$this->indexCreator               = new IndexCreator(
			$this->playlistBuilderFactoryMock,
			$this->templateSelectorMock,
			$this->indexFileMock,
			$this->templatePreparerMock,
			$this->templateServiceMock
		);
	}

	/**
	 * @throws Exception
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testCreateForReleasedPlayerProcessesCorrectly(): void
	{
		$playerEntityMock      = $this->createMock(PlayerEntity::class);
		$configMock            = $this->createMock(Config::class);
		$playlistStructureMock = $this->createMock(PlaylistStructureInterface::class);
		$playlistBuilderMock   = $this->createMock(PlaylistBuilderInterface::class);

		$configMock->method('getConfigValue')
			->with('path_smil_index', 'player')
			->willReturn('/path/to/index');
		$playerEntityMock->method('getUuid')->willReturn('uuid123');
		$playerEntityMock->method('getPlaylistId')->willReturn(456);

		$this->playlistBuilderFactoryMock->expects($this->once())->method('createBuilder')
			->with($playerEntityMock)
			->willReturn($playlistBuilderMock);

		$playlistBuilderMock->expects($this->once())->method('buildPlaylist')
			->willReturn($playlistStructureMock);

		$this->templateSelectorMock->expects($this->once())->method('select')
			->with($playerEntityMock)
			->willReturn(TemplateIndexFiles::GARLIC);

		$this->templatePreparerMock->expects($this->once())->method('setPlayerEntity')
			->with($playerEntityMock)
			->willReturnSelf();
		$this->templatePreparerMock->expects($this->once())->method('setPlaylistStructure')
			->with($playlistStructureMock)
			->willReturnSelf();
		$this->templatePreparerMock->expects($this->once())->method('prepare')
			->with(TemplateIndexFiles::GARLIC)
			->willReturnSelf();
		$this->templatePreparerMock->expects($this->once())->method('getTemplateData')
			->willReturn(['key' => 'value']);

		$this->templateServiceMock->expects($this->once())->method('render')
			->with('player/index/' . TemplateIndexFiles::GARLIC->value, ['key' => 'value'])
			->willReturn('smil_content');

		$filepath = '/path/to/index/uuid123/456.smil';
		$this->indexFileMock->expects($this->once())
			->method('setIndexFilePath')
			->with($filepath)
			->willReturnSelf();

		$this->indexFileMock->expects($this->once())
			->method('handleIndexFile')
			->with('smil_content');

		$this->indexCreator->createForReleasedPlayer($playerEntityMock, $configMock);

		$this->assertSame($filepath, $this->indexCreator->getIndexFilePath());
	}
}
