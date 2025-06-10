<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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


namespace Tests\Unit\Modules\Mediapool\Utils;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\Utils\AbstractMediaHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

class ConcreteMediaHandler extends AbstractMediaHandler
{
	public function checkFileBeforeUpload(int $size): void {}
	public function checkFileAfterUpload(string $filePath): void {}
	public function createThumbnail(string $filePath) {}
}
class AbstractMediaHandlerTest extends TestCase
{

	private readonly AbstractMediaHandler $concreteMediaHandler;
	private readonly Filesystem $filesystemMock;
	private readonly Config $configMock;

	/**
	 * @throws Exception
	 * @throws CoreException
	 */
	protected function setUp(): void
	{
		$this->configMock           = $this->createMock(Config::class);
		$this->filesystemMock = $this->createMock(Filesystem::class);

		$this->configMock->method('getConfigValue')
			->willReturnMap([
				['width', 'mediapool', 'max_resolution', 3840],
				['height', 'mediapool', 'max_resolution', 3840],
				['thumb_width', 'mediapool', 'dimensions', 150],
				['thumb_height', 'mediapool', 'dimensions', 150],
				['uploads', 'mediapool', 'directories', '/uploads'],
				['thumbnails', 'mediapool', 'directories', '/thumbnails'],
				['originals', 'mediapool', 'directories', '/originals'],
				['previews', 'mediapool', 'directories', '/previews'],
				['icons', 'mediapool', 'directories', '/icons']
			]);

		$this->concreteMediaHandler = new ConcreteMediaHandler($this->configMock, $this->filesystemMock);
	}

	#[Group('units')]
	public function testSomeGetters()
	{
		$this->assertEmpty($this->concreteMediaHandler->getDimensions());
		$this->assertEmpty($this->concreteMediaHandler->getFileSize());
		$this->assertEmpty($this->concreteMediaHandler->getConfigData());
		$this->assertEquals(0.0, $this->concreteMediaHandler->getDuration());}

	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testExistsSucceed()
	{
		$this->filesystemMock->method('fileExists')->willReturn(true);
		$this->assertTrue($this->concreteMediaHandler->exists('/path/to/file'));
	}

	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testExistsFailed()
	{
		$this->filesystemMock->method('fileExists')->willReturn(false);
		$this->assertFalse($this->concreteMediaHandler->exists('/path/to/file'));
	}

	/**
	 * @throws FilesystemException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testDetermineNewFilenameThrowsException()
	{
		$this->filesystemMock->method('fileExists')->willReturn(false);
		$this->expectException(ModuleException::class);
		$this->concreteMediaHandler->determineNewFilename('/path/to/file');
	}

	/**
	 * @throws FilesystemException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testDetermineNewFilenameThrowsException2()
	{
		$this->filesystemMock->method('fileExists')->willReturn(true);
		$this->filesystemMock->method('readStream')->willReturn(false);
		$this->expectException(ModuleException::class);
		$this->concreteMediaHandler->determineNewFilename('/path/to/file');
	}


	/**
	 * @throws FilesystemException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testDetermineNewFilenameSucceed()
	{
		$this->filesystemMock->method('fileExists')->willReturn(true);
		$this->filesystemMock->method('readStream')->willReturn(fopen('php://memory', 'r+'));
		fwrite($this->filesystemMock->readStream('/path/to/file'), 'file content');
		rewind($this->filesystemMock->readStream('/path/to/file'));

		$hash = $this->concreteMediaHandler->determineNewFilename('/path/to/file');
		$this->assertEquals(hash('sha256', 'file content'), $hash);
	}

	#[Group('units')]
	public function testDetermineNewFilePathReturnsCorrectPathWithExtension()
	{
		$oldFilePath = '/path/to/file.txt';
		$filehash = '1234567890abcdef';
		$extension = 'txt';

		$newFilePath = $this->concreteMediaHandler->determineNewFilePath($oldFilePath, $filehash, $extension);

		$this->assertEquals('/path/to/1234567890abcdef.txt', $newFilePath);
	}

	#[Group('units')]
	public function testDetermineNewFilePathReturnsCorrectPathWithoutExtension()
	{
		$oldFilePath = '/path/to/file';
		$filehash = '1234567890abcdef';
		$extension = 'txt';

		$newFilePath = $this->concreteMediaHandler->determineNewFilePath($oldFilePath, $filehash, $extension);

		$this->assertEquals('/path/to/1234567890abcdef.txt', $newFilePath);
	}

	#[Group('units')]
	public function testDetermineNewFilePathNormalizesJpegExtension()
	{
		$oldFilePath = '/path/to/file.jpeg';
		$filehash = '1234567890abcdef';
		$extension = 'jpg';

		$newFilePath = $this->concreteMediaHandler->determineNewFilePath($oldFilePath, $filehash, $extension);

		$this->assertEquals('/path/to/1234567890abcdef.jpg', $newFilePath);
	}

	#[Group('units')]
	public function testDetermineNewFilePath_UsesMimeTypeWhenNoExtension()
	{
		$oldFilePath = '/path/to/file';
		$filehash = '1234567890abcdef';
		$extension = 'jpg';

		$newFilePath = $this->concreteMediaHandler->determineNewFilePath($oldFilePath, $filehash, $extension);

		$this->assertEquals('/path/to/1234567890abcdef.jpg', $newFilePath);
	}

	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testRemoveUploadedFileDeletesFile()
	{
		$this->filesystemMock->expects($this->once())->method('delete')->with('/path/to/file');
		$this->concreteMediaHandler->removeUploadedFile('/path/to/file');
	}

	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testRenameMovesFile()
	{
		$this->filesystemMock->expects($this->once())->method('move')->with('/old/path', '/new/path');
		$this->concreteMediaHandler->rename('/old/path', '/new/path');
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testUploadFromLocalSuccessfullyUploadsFile()
	{
		$uploadedFileMock = $this->createMock(UploadedFileInterface::class);
		$uploadedFileMock->method('getClientFilename')->willReturn('testfile.jpg');
		$uploadedFileMock->expects($this->once())->method('moveTo');

		$result = $this->concreteMediaHandler->uploadFromLocal($uploadedFileMock);

		$this->assertEquals('//originals/testfile.jpg', $result);
	}

	/**
	 * @throws GuzzleException|Exception
	 */
	#[Group('units')]
	public function testUploadFromExternalReturnsCorrectPath()
	{
		$clientMock = $this->createMock(Client::class);
		$this->configMock->expects($this->once())->method('getPaths')
			->with('systemDir')
			->willReturn('/absolute/path/to');

		$clientMock->expects($this->once())->method('request')
			->with('GET', 'http://example.com/file', ['sink' => '/absolute/path/to//originals/file']);

		$result = $this->concreteMediaHandler->uploadFromExternal($clientMock, 'http://example.com/file');
		$this->assertEquals('//originals/file', $result);
	}
}
