<?php

namespace Tests\Unit\Modules\Player\IndexCreation;

use App\Framework\Exceptions\ModuleException;
use App\Modules\Player\IndexCreation\IndexFile;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class IndexFileTest extends TestCase
{
	private readonly FileSystem&MockObject $fileSystemMock;
	private readonly LoggerInterface&MockObject $loggerMock;
	private IndexFile $indexFile;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->fileSystemMock = $this->createMock(Filesystem::class);
		$this->loggerMock     = $this->createMock(LoggerInterface::class);

		$this->indexFile = new IndexFile($this->fileSystemMock, $this->loggerMock);
	}

	/**
	 * @throws ModuleException
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testHandleIndexFileNewContent(): void
	{
		$this->indexFile->setIndexFilePath('/some/path');

		$this->fileSystemMock->expects($this->once())->method('fileExists')
			->with('/some/path')
			->willReturn(true);

		$this->fileSystemMock->expects($this->once())->method('read')
			->with('/some/path')
			->willReturn('old content');

		$this->fileSystemMock->expects($this->once())->method('write')
			->with('/some/path', 'new content');

		$this->indexFile->handleIndexFile('new content');
	}

	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testHandleIndexFileWithEmptyOldContentThrowsException(): void
	{
		$this->indexFile->setIndexFilePath('/some/path');

		$this->fileSystemMock
			->expects($this->once())->method('fileExists')
			->with('/some/path')
			->willReturn(false);

		$this->loggerMock->expects($this->once())->method('warning')
			->with($this->stringContains('Index content generation failed'));

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Index content generation failed and no old index file present');

		$this->indexFile->handleIndexFile('');
	}

	/**
	 * @throws ModuleException
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testHandleIndexFileDoesNotWriteWhenNewContentMatchesOld(): void
	{
		$this->indexFile->setIndexFilePath('/some/path');

		$this->fileSystemMock
			->expects($this->once())
			->method('fileExists')
			->with('/some/path')
			->willReturn(true);

		$this->fileSystemMock
			->expects($this->once())
			->method('read')
			->with('/some/path')
			->willReturn('same content');

		$this->fileSystemMock
			->expects($this->never())
			->method('write');

		$this->indexFile->handleIndexFile('same content');
	}
}
