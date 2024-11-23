<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

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