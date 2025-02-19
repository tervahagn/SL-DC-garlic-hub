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


namespace Tests\Unit\Framework\Utils;

use App\Framework\Core\Config\Config;
use App\Framework\Core\ShellExecutor;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Utils\Ffmpeg;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class FfmpegTest extends TestCase
{
	private readonly Config $configMock;
	private readonly Filesystem $filesystemMock;
	private readonly ShellExecutor $shellExecutorMock;

	private readonly Ffmpeg $ffmpeg;

	protected function setUp(): void
	{
		$this->configMock        = $this->createMock(Config::class);
		$this->filesystemMock    = $this->createMock(Filesystem::class);
		$this->shellExecutorMock = $this->createMock(ShellExecutor::class);

		$this->ffmpeg = new Ffmpeg($this->configMock, $this->filesystemMock, $this->shellExecutorMock);
	}

	#[Group('units')]
	public function testSetMetaData()
	{
		$metadata = ['test' => 'test'];
		$this->ffmpeg->setMetadata($metadata);

		$this->assertEquals($metadata, $this->ffmpeg->getMetadata());
	}

	#[Group('units')]
	public function testInitWithValidFilePath()
	{
		$this->filesystemMock->method('fileExists')->willReturn(true);
		$this->configMock->expects($this->exactly(3))->method('getConfigValue')->willReturn('');
		$this->configMock->expects($this->once())->method('getPaths');
		$this->shellExecutorMock->method('executeSimple')->willReturn('{"format": {"filename": "test.mp4", "size": 12345, "format_name": "mp4", "duration": 60, "start_time": 0}, "streams": [{"codec_type": "video", "codec_name": "h264", "width": 1920, "height": 1080}]}');

		$this->ffmpeg->init('/path/to/video.mp4');

		$this->assertEquals('test.mp4', $this->ffmpeg->getMediaProperties()['filename']);
	}

	#[Group('units')]
	public function testInitWithNonExistentFileThrowsException()
	{
		$this->filesystemMock->method('fileExists')->willReturn(false);
		$this->configMock->expects($this->exactly(3))->method('getConfigValue')->willReturn('');

		$this->expectException(FrameworkException::class);
		$this->expectExceptionMessage('File does not exist: /path/to/video.mp4');

		$this->ffmpeg->init('/path/to/video.mp4');
	}

	#[Group('units')]
	public function testInitProbeFileNull()
	{
		$this->filesystemMock->method('fileExists')->willReturn(true);
		$this->configMock->expects($this->exactly(3))->method('getConfigValue')->willReturn('');
		$this->configMock->expects($this->once())->method('getPaths');
		$this->shellExecutorMock->method('executeSimple')->willReturn('');

		$this->expectException(FrameworkException::class);
		$this->expectExceptionMessage('Probing media file failed. Unsupported file type for file /path/to/video.mp4. Using command: ');

		$this->ffmpeg->init('/path/to/video.mp4');
	}

	#[Group('units')]
	public function testCreateVidCapWithValidVideo()
	{
		$this->ffmpeg->setMediaProperties(['filename' => 'test.mp4', 'video_codec' => 'h264']);
		$this->filesystemMock->method('fileExists')->willReturn(true);

		$this->shellExecutorMock->method('executeSimple')
			->willReturn('{"streams": [{"codec_type": "video", "codec_name": "h264", "width": 1920, "height": 1080}]}');

		$vidcapPath = $this->ffmpeg->createVidCap('/path/to/destination');

		$this->assertEquals('/path/to/destination/vid_1.jpg', $vidcapPath);
	}

	#[Group('units')]
	public function testCreateVidCapFails()
	{
		$this->ffmpeg->setMediaProperties(['filename' => 'test.mp4', 'video_codec' => 'h264']);
		$this->filesystemMock->method('fileExists')
			->willReturn(false);

		$this->shellExecutorMock->method('executeSimple')
			->willReturn('{"streams": [
			{"codec_type": "video",
			 "codec_name": "h264",
			  "width": 1920, "height": 1080}
			]}');

		$this->expectException(FrameworkException::class);
		$this->expectExceptionMessage('Vid cap /path/to/destination/vid_1.jpg not found');
		$vidcapPath = $this->ffmpeg->createVidCap('/path/to/destination');

		$this->assertEquals('/path/to/destination/vid_1.jpg', $vidcapPath);
	}


	#[Group('units')]
	public function testCreateVidCapWithNoVideoStreamThrowsException()
	{
		$this->ffmpeg->setMediaProperties(['filename' => 'test.mp4', 'video_codec' => '']);

		$this->expectException(FrameworkException::class);
		$this->expectExceptionMessage('Can create video captions of test.mp4. File has no readable video stream');

		$this->ffmpeg->createVidCap('/path/to/destination');
	}
}
