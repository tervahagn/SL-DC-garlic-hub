<?php

namespace Tests\Unit\Modules\Mediapool\Utils;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Media\Ffmpeg;
use App\Modules\Mediapool\Utils\Audio;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AudioTest extends TestCase
{
	private Filesystem&MockObject $filesystemMock;
	private Ffmpeg&MockObject $ffmpegMock;
	private Audio $audio;

	/**
	 * @throws CoreException
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$configMock           = $this->createMock(Config::class);
		$this->filesystemMock = $this->createMock(\League\Flysystem\Filesystem::class);
		$this->ffmpegMock     = $this->createMock(Ffmpeg::class);

		$configMock->method('getConfigValue')
			->willReturnMap([
				['width', 'mediapool', 'max_resolution', 3840],
				['height', 'mediapool', 'max_resolution', 3840],
				['thumb_width', 'mediapool', 'dimensions', 150],
				['thumb_height', 'mediapool', 'dimensions', 150],
				['uploads', 'mediapool', 'directories', '/uploads'],
				['thumbnails', 'mediapool', 'directories', '/thumbnails'],
				['originals', 'mediapool', 'directories', '/originals'],
				['previews', 'mediapool', 'directories', '/previews'],
				['icons', 'mediapool', 'directories', '/icons'],
				['audios', 'mediapool', 'max_file_sizes', 1073741824]
			]);

		$this->audio = new Audio($configMock, $this->filesystemMock, $this->ffmpegMock);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testCheckFileBeforeUploadValidSizeDoesNotThrowException(): void
	{
		$this->expectNotToPerformAssertions();
		$this->audio->checkFileBeforeUpload(1073741824);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testCheckFileBeforeUploadExceedsMaxSizeThrowsModuleException(): void
	{
		$this->expectException(ModuleException::class);
		$this->audio->checkFileBeforeUpload(1073741824 + 1);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws FilesystemException
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testCheckFileAfterUploadFileExistsDoesNotThrowException(): void
	{
		$this->filesystemMock->method('fileExists')->willReturn(true);
		$this->filesystemMock->method('fileSize')->willReturn(1073741824);
		$this->ffmpegMock->method('init')->with('/path/to/file');
		$properties = [];
		$this->ffmpegMock->method('getMediaProperties')->willReturn($properties);

		$this->audio->checkFileAfterUpload('/path/to/file');
	}

	/**
	 * @throws CoreException
	 * @throws FilesystemException
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testCheckFileAfterUploadFileNotExistsThrowsModuleException(): void
	{
		$this->expectException(ModuleException::class);
		$this->filesystemMock->method('fileExists')->willReturn(false);
		$this->audio->checkFileAfterUpload('/path/to/file');
	}

	/**
	 * @throws CoreException
	 * @throws FilesystemException
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testCheckFileAfterUploadExceedsMaxSizeThrowsModuleException(): void
	{
		$this->expectException(ModuleException::class);
		$this->filesystemMock->method('fileExists')->willReturn(true);
		$this->filesystemMock->method('fileSize')->willReturn(1073741824 + 1);
		$this->audio->checkFileAfterUpload('/path/to/file');
	}



	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testCreateThumbnail(): void
	{
		$this->filesystemMock->method('copy')
			->with('//icons/audio.svg', '//thumbnails/file.svg');


		$this->audio->createThumbnail('/path/to/file.mp3');
	}

}
