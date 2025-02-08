<?php


namespace Tests\Unit\Modules\Mediapool\Utils;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\Utils\Pdf;
use Imagick;
use ImagickException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class PdfTest extends TestCase
{
	private readonly Pdf $pdf;
	private readonly Filesystem $filesystemMock;
	private readonly Imagick $imagickMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$configMock = $this->createMock(Config::class);
		$this->filesystemMock = $this->createMock(Filesystem::class);
		$this->imagickMock = $this->createMock(Imagick::class);

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
				['documents', 'mediapool', 'max_file_sizes', 1073741824]
			]);

		$this->pdf = new Pdf($configMock, $this->filesystemMock, $this->imagickMock);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testCheckFileBeforeUpload_ValidSize_DoesNotThrowException()
	{
		$this->expectNotToPerformAssertions();
		$this->pdf->checkFileBeforeUpload(1073741824);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testCheckFileBeforeUpload_ExceedsMaxSize_ThrowsModuleException()
	{
		$this->expectException(ModuleException::class);
		$this->pdf->checkFileBeforeUpload(1073741824 + 1);
	}

	/**
	 * @throws ModuleException
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testCheckFileAfterUpload_FileExists_DoesNotThrowException()
	{
		$this->filesystemMock->method('fileExists')->willReturn(true);
		$this->filesystemMock->method('fileSize')->willReturn(1073741824);

		$this->expectNotToPerformAssertions();
		$this->pdf->checkFileAfterUpload('/path/to/file');
	}

	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testCheckFileAfterUpload_FileNotExists_ThrowsModuleException()
	{
		$this->expectException(ModuleException::class);
		$this->filesystemMock->method('fileExists')->willReturn(false);
		$this->pdf->checkFileAfterUpload('/path/to/file');
	}

	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testCheckFileAfterUpload_ExceedsMaxSize_ThrowsModuleException()
	{
		$this->expectException(ModuleException::class);
		$this->filesystemMock->method('fileExists')->willReturn(true);
		$this->filesystemMock->method('fileSize')->willReturn(1073741824 + 1);
		$this->pdf->checkFileAfterUpload('/path/to/file');
	}

	/**
	 * @throws ImagickException
	 */
	#[Group('units')]
	public function testCreateThumbnail_CreatesThumbnail()
	{
		$this->imagickMock->expects($this->once())->method('setResolution')->with(150, 150);
		$this->imagickMock->expects($this->once())->method('readImage')->with($this->stringContains('[0]'));
		$this->imagickMock->expects($this->once())->method('setImageAlphaChannel')->with(Imagick::ALPHACHANNEL_REMOVE);
		$this->imagickMock->expects($this->once())->method('setImageFormat')->with('jpg');
		$this->imagickMock->expects($this->once())->method('thumbnailImage')->with(150, 150, true);
		$this->imagickMock->expects($this->once())->method('writeImage');

		$this->pdf->createThumbnail('/path/to/file.pdf');
	}
}
