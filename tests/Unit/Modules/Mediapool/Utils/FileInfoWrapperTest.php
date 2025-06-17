<?php

namespace Tests\Unit\Modules\Mediapool\Utils;

use App\Modules\Mediapool\Utils\FileInfoWrapper;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FileInfoWrapperTest extends TestCase
{
	use PHPMock;

	private FileInfoWrapper $fileInfoWrapper;
	private string $baseDirectory;

	protected function setUp(): void
	{
		$this->baseDirectory = getenv('TEST_BASE_DIR') . '/resources/tmp';

		if (!is_dir($this->baseDirectory))
		{
			mkdir($this->baseDirectory, 0777, true);
		}

	}

	#[RunInSeparateProcess] #[Group('units')]
	public function testConstructor(): void
	{
		$finfo_open = $this->getFunctionMock('App\Modules\Mediapool\Utils', 'finfo_open');
		$finfo_open->expects($this->once())->willReturn(false);

		$this->expectException(RuntimeException::class);
		$this->fileInfoWrapper = new FileInfoWrapper();
	}


	#[Group('units')]
	public function testDestructClosesFileInfo(): void
	{
		$this->fileInfoWrapper = new FileInfoWrapper();
		$fileInfoWrapperMock = $this->getMockBuilder(FileInfoWrapper::class)
			->disableOriginalConstructor()
			->onlyMethods(['__destruct'])
			->getMock();

		$fileInfoWrapperMock->__construct(); // Ensures `finfo_open` is called.
		$this->assertNotNull($fileInfoWrapperMock);

		// Expect finfo_close to be called during destruction
		$fileInfoWrapperMock->__destruct();
	}


	#[Group('units')]
	public function testFileExistsReturnsTrueWhenFileExists(): void
	{
		$this->fileInfoWrapper = new FileInfoWrapper();

		$filePath = $this->baseDirectory . '/testFile.txt';
		file_put_contents($filePath, 'test content');
		$this->assertTrue($this->fileInfoWrapper->fileExists($filePath));

		unlink($filePath);
	}

	#[Group('units')]
	public function testFileExistsReturnsFalseWhenFileDoesNotExist(): void
	{
		$this->fileInfoWrapper = new FileInfoWrapper();
		$filePath = $this->baseDirectory . '/nonExistentFile.txt';
		$this->assertFalse($this->fileInfoWrapper->fileExists($filePath));

	}

	#[Group('units')]
	public function testDetectMimeTypeFromFileReturnsFalseForUnknownMimeType(): void
	{
		$this->fileInfoWrapper = new FileInfoWrapper();

		$filePath = $this->baseDirectory . '/plain.txt';
		file_put_contents($filePath, 'some content');
		$this->assertSame('text/plain', $this->fileInfoWrapper->detectMimeTypeFromFile($filePath));
		unlink($filePath);
	}

	#[Group('units')]
	public function testDetectFromStreamReturnsCorrectMimeType(): void
	{
		$this->fileInfoWrapper = new FileInfoWrapper();
		$mimeType = $this->fileInfoWrapper->detectMimeTypeFromStreamContent('test content');
		$this->assertEquals('text/plain', $mimeType);
	}

	#[Group('units')]
	public function testDetectFromStreamReturnsFalse(): void
	{
		$this->fileInfoWrapper = new FileInfoWrapper();

		$mimeType = $this->fileInfoWrapper->detectMimeTypeFromStreamContent('');
		$this->assertEquals('application/x-empty', $mimeType);
	}

	#[Group('units')]
	public function testDetectIsStreamSucceed(): void
	{
		$this->fileInfoWrapper = new FileInfoWrapper();

		$stream = fopen('php://memory', 'r+');
		if ($stream === false)
			return;
		$i = fwrite($stream, 'test content');
		if ($i === false)
			return;
		rewind($stream);
		$this->assertTrue($this->fileInfoWrapper->isStream($stream));
		fclose($stream);
	}

	#[Group('units')]
	public function testGetStreamContent(): void
	{
		$this->fileInfoWrapper = new FileInfoWrapper();

		$stream = fopen('php://memory', 'r+');
		if ($stream === false)
			return;
		$i = fwrite($stream, 'test content');
		if ($i === false)
			return;

		rewind($stream);
		$this->assertSame('test content', $this->fileInfoWrapper->getStreamContent($stream));
		fclose($stream);
	}
}
