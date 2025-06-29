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


namespace Tests\Unit\Framework\Media;

use App\Framework\Core\Config\Config;
use App\Framework\Core\ShellExecutor;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Media\Ffmpeg;
use App\Framework\Media\MediaProperties;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FfmpegTest extends TestCase
{
	private Config&MockObject $configMock;
	private Filesystem&MockObject $filesystemMock;
	private ShellExecutor&MockObject $shellExecutorMock;
	private MediaProperties&MockObject $mediaPropertiesMock;
	private Ffmpeg $ffmpeg;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->configMock          = $this->createMock(Config::class);
		$this->filesystemMock      = $this->createMock(Filesystem::class);
		$this->mediaPropertiesMock = $this->createMock(MediaProperties::class);
		$this->shellExecutorMock   = $this->createMock(ShellExecutor::class);

		$this->ffmpeg = new Ffmpeg($this->configMock, $this->filesystemMock, $this->mediaPropertiesMock, $this->shellExecutorMock);
	}

	#[Group('units')]
	public function testSetMetaData(): void
	{
		$metadata = ['test' => 'test'];
		$this->ffmpeg->setMetadata($metadata);

		$this->assertEquals($metadata, $this->ffmpeg->getMetadata());
	}

	/**
	 * @throws FilesystemException
	 * @throws CoreException
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testInitWithValidFilePath(): void
	{
		$this->filesystemMock->method('fileExists')->willReturn(true);
		$this->configMock->expects($this->exactly(3))->method('getConfigValue')->willReturn('');
		$this->configMock->expects($this->once())->method('getPaths');
		$this->shellExecutorMock->method('executeSimple')->willReturn('{"format": {"filename": "test.mp4", "size": 12345, "format_name": "mp4", "duration": 60, "start_time": 0}, "streams": [{"codec_type": "video", "codec_name": "h264", "width": 1920, "height": 1080}]}');

		$this->ffmpeg->init('/path/to/video.mp4');
		$this->mediaPropertiesMock->method('toArray')->willReturn(['filename' => 'test.mp4']);
		$this->assertEquals('test.mp4', $this->ffmpeg->getMediaProperties()['filename']);
	}

	#[Group('units')]
	public function testGetDuration(): void
	{
		$this->assertSame(0.0, $this->ffmpeg->getDuration());
		$this->mediaPropertiesMock->method('getDuration')->willReturn(10.0);
		$this->assertSame(10.0, $this->ffmpeg->getDuration());
	}

	/**
	 * @throws FilesystemException
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testInitWithNonExistentFileThrowsException(): void
	{
		$this->filesystemMock->method('fileExists')->willReturn(false);
		$this->configMock->expects($this->exactly(3))->method('getConfigValue')->willReturn('');

		$this->expectException(FrameworkException::class);
		$this->expectExceptionMessage('File does not exist: /path/to/video.mp4');

		$this->ffmpeg->init('/path/to/video.mp4');
	}

	/**
	 * @throws FilesystemException
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testInitProbeFileNull(): void
	{
		$this->filesystemMock->method('fileExists')->willReturn(true);
		$this->configMock->expects($this->exactly(3))->method('getConfigValue')->willReturn('');
		$this->configMock->expects($this->once())->method('getPaths');
		$this->shellExecutorMock->method('executeSimple')->willReturn('');

		$this->expectException(FrameworkException::class);
		$this->expectExceptionMessage('Probing media file failed. Unsupported file type for file /path/to/video.mp4. Using command: ');

		$this->ffmpeg->init('/path/to/video.mp4');
	}

	/**
	 * @throws FilesystemException
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testCreateVideoThumbnailWithValidVideo(): void
	{
		$this->filesystemMock->method('fileExists')->willReturn(true);
		$this->mediaPropertiesMock->method('hasVideoStream')->willReturn(true);
		$this->shellExecutorMock->method('executeSimple')
			->willReturn('{"streams": [{"codec_type": "video", "codec_name": "h264", "width": 1920, "height": 1080}]}');

		$vidcapPath = $this->ffmpeg->createVideoThumbnail('/path/to/destination');

		$this->assertEquals('/path/to/destination/vid_1.jpg', $vidcapPath);
	}

	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testCreateVideoThumbnailFails(): void
	{
		$this->mediaPropertiesMock->method('hasVideoStream')->willReturn(true);
		$this->filesystemMock->method('fileExists')
			->willReturn(false);

		$this->shellExecutorMock->method('executeSimple')
			->willReturn('{"streams": [
			{"codec_type": "video",
			 "codec_name": "h264",
			  "width": 1920, "height": 1080}
			]}');

		$this->expectException(FrameworkException::class);
		$this->expectExceptionMessage('Thumbnail /path/to/destination/vid_1.jpg not found');
		$vidcapPath = $this->ffmpeg->createVideoThumbnail('/path/to/destination');

		$this->assertEquals('/path/to/destination/vid_1.jpg', $vidcapPath);
	}


	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testCreateVideoThumbnailWithNoVideoStreamThrowsException(): void
	{
		$this->mediaPropertiesMock->method('hasVideoStream')->willReturn(false);

		$this->expectException(FrameworkException::class);
		$this->expectExceptionMessage('Cannot create video thumbnail for . File has no readable video stream');

		$this->ffmpeg->createVideoThumbnail('/path/to/destination');
	}
}
