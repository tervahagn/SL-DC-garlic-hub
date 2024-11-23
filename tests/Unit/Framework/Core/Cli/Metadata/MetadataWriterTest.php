<?php

namespace Tests\Unit\Framework\Core\Cli\Metadata;

use App\Framework\Core\Cli\Metadata\MetadataWriter;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToWriteFile;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class MetadataWriterTest extends TestCase
{
    private FilesystemOperator $filesystemMock;
    private MetadataWriter $metadataWriter;

    protected function setUp(): void
    {
        $this->filesystemMock = $this->createMock(FilesystemOperator::class);
        $this->metadataWriter = new MetadataWriter($this->filesystemMock, 'path/to/output.json');
    }

    #[Group('units')]
    public function testWriteWritesJsonToFile(): void
    {
        $commandData = [
            'command' => 'testCommand',
            'description' => 'This is a test command',
        ];

        $expectedJson = json_encode($commandData, JSON_PRETTY_PRINT);

        $this->filesystemMock
            ->expects($this->once())
            ->method('write')
            ->with('path/to/output.json', $expectedJson);

        $this->metadataWriter->write($commandData);
    }

    #[Group('units')]
    public function testWriteThrowsFilesystemException(): void
    {
        $this->expectException(FilesystemException::class);

        $commandData = [
            'command' => 'testCommand',
            'description' => 'This is a test command',
        ];

        $this->filesystemMock
            ->method('write')
            ->willThrowException(new UnableToWriteFile('Write operation failed'));

        $this->metadataWriter->write($commandData);
    }
}