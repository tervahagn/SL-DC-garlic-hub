<?php

namespace Tests\Unit\Modules\Playlists\Helper\ExportSmil;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Modules\Playlists\Helper\ExportSmil\LocalWriter;
use App\Modules\Playlists\Helper\ExportSmil\PlaylistContent;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LocalWriterTest extends TestCase
{
	private readonly Filesystem&MockObject $fileSystemMock;
	private LocalWriter $writer;

	/**
	 * @throws Exception
	 * @throws CoreException
	 */
	protected function setUp(): void
	{
		$configMock = $this->createMock(Config::class);
		$this->fileSystemMock = $this->createMock(Filesystem::class);
		$configMock->method('getConfigValue')
			->with('path_playlists', 'playlists')
			->willReturn('path/to/playlists');
		$this->writer = new LocalWriter($configMock, $this->fileSystemMock);
	}

	/**
	 * @throws FilesystemException
	 * @throws Exception
	 */
	#[Group('units')]
	public function testWriteSMILFilesSuccessfully(): void
	{
		$playlistId = 123;
		$this->writer->initExport($playlistId);

		$playlistContentMock = $this->createMock(PlaylistContent::class);
		$playlistContentMock->method('getContentPrefetch')
			->willReturn('prefetch content');
		$playlistContentMock->method('getContentElements')
			->willReturn('elements content');
		$playlistContentMock->method('getContentExclusive')
			->willReturn('exclusive content');

		$playlistPath = 'path/to/playlists/' . $playlistId;
		$this->fileSystemMock->expects($this->once())
			->method('createDirectory')
			->with($playlistPath);

		$this->fileSystemMock->expects($this->exactly(3))
			->method('write')
			->willReturnMap([
				[$playlistPath . '/prefetch.smil', 'prefetch content'],
				[$playlistPath . '/items.smil', 'elements content'],
				[$playlistPath . '/exclusive.smil', 'exclusive content']
			]);

		$this->writer->writeSMILFiles($playlistContentMock);
	}

}
