<?php

namespace Tests\Unit\Modules\Mediapool\Utils;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Modules\Mediapool\Utils\Image;
use App\Modules\Mediapool\Utils\ImagickFactory;
use App\Modules\Mediapool\Utils\MediaHandlerFactory;
use App\Modules\Mediapool\Utils\Miscellaneous;
use App\Modules\Mediapool\Utils\Pdf;
use App\Modules\Mediapool\Utils\Video;
use App\Modules\Mediapool\Utils\Widget;
use App\Modules\Mediapool\Utils\ZipFilesystemFactory;
use Imagick;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class MediaHandlerFactoryTest extends TestCase
{
	private readonly Config $configMock;
	private readonly ImagickFactory $imagickFactoryMock;
	private readonly MediaHandlerFactory $mediaHandlerFactory;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->configMock = $this->createMock(Config::class);
		$filesystemMock = $this->createMock(Filesystem::class);
		$zipFilesystemFactoryMock = $this->createMock(ZipFilesystemFactory::class);
		$this->imagickFactoryMock = $this->createMock(ImagickFactory::class);

		$this->mediaHandlerFactory = new MediaHandlerFactory(
			$this->configMock,
			$filesystemMock,
			$zipFilesystemFactoryMock,
			$this->imagickFactoryMock
		);
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testCreateHandlerReturnsImageForImageMimeType()
	{
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
				['icons', 'mediapool', 'directories', '/icons'],
				['images', 'mediapool', 'max_file_sizes', 20971520]
			]);

		$this->imagickFactoryMock->method('createImagick')->willReturn(new Imagick());
		$handler = $this->mediaHandlerFactory->createHandler('image/jpeg');
		$this->assertInstanceOf(Image::class, $handler);
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testCreateHandlerReturnsVideoForVideoMimeType()
	{
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
				['icons', 'mediapool', 'directories', '/icons'],
				['videos', 'mediapool', 'max_file_sizes', 1073741824]
			]);

		$this->imagickFactoryMock->method('createImagick')->willReturn(new Imagick());
		$handler = $this->mediaHandlerFactory->createHandler('video/mp4');
		$this->assertInstanceOf(Video::class, $handler);
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testCreateHandlerReturnsPdfForPdfMimeType()
	{
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
				['icons', 'mediapool', 'directories', '/icons'],
				['documents', 'mediapool', 'max_file_sizes', 20971520]
			]);

		$this->imagickFactoryMock->method('createImagick')->willReturn(new Imagick());
		$handler = $this->mediaHandlerFactory->createHandler('application/pdf');
		$this->assertInstanceOf(Pdf::class, $handler);
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testCreateHandlerReturnsWidgetForWidgetMimeType()
	{
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
				['icons', 'mediapool', 'directories', '/icons'],
				['downloads', 'mediapool', 'max_file_sizes', 1073741824]
			]);

		$this->imagickFactoryMock->method('createImagick')->willReturn(new Imagick());
		$handler = $this->mediaHandlerFactory->createHandler('application/widget');
		$this->assertInstanceOf(Widget::class, $handler);
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testCreateHandlerReturnsMiscellaneousForMiscellaneousMimeType()
	{
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
				['icons', 'mediapool', 'directories', '/icons'],
				['downloads', 'mediapool', 'max_file_sizes', 1073741824]
			]);

		$handler = $this->mediaHandlerFactory->createHandler('application/zip');
		$this->assertInstanceOf(Miscellaneous::class, $handler);
	}

	#[Group('units')]
	public function testCreateHandlerThrowsExceptionForUnsupportedMimeType()
	{
		$this->expectException(CoreException::class);
		$this->mediaHandlerFactory->createHandler('unsupported/mime');
	}
}
