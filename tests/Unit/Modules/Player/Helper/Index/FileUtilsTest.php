<?php

namespace Tests\Unit\Modules\Player\Helper\Index;

use App\Framework\Exceptions\ModuleException;
use App\Modules\Player\Helper\Index\FileUtils;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class FileUtilsTest extends TestCase
{
	use PHPMock;

	private FileUtils $fileUtils;
	private string $filePath;

	protected function setUp(): void
	{
		$this->fileUtils = new FileUtils();
		$baseDir = getenv('TEST_BASE_DIR') . '/resources/tmp';
		$this->filePath = $baseDir.'/test.bin';
		file_put_contents($this->filePath, "\xDE\xAD\xBE\xEF");
	}

	protected function tearDown(): void
	{
		unlink($this->filePath);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testGetFileMethods(): void
	{
		$this->assertSame(filemtime($this->filePath), $this->fileUtils->getFileMTime($this->filePath));
		$this->assertSame(4, $this->fileUtils->getFileSize($this->filePath));
		$this->assertSame('2f249230a8e7c2bf6005ccd2679259ec', $this->fileUtils->getETag($this->filePath));

		$this->fileUtils->createStream($this->filePath);
	}

	#[RunInSeparateProcess] #[Group('units')]
	public function testGetFileMNameFail(): void
	{
		$filemTime = $this->getFunctionMock('App\Modules\Player\Helper\Index', 'filemTime');
		$filemTime->expects($this->once())->willReturn(false);

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('FileMTime error with: '.$this->filePath);
		$this->fileUtils->getFileMTime($this->filePath);
	}

	#[RunInSeparateProcess] #[Group('units')]
	public function testGetEtag(): void
	{
		$file_get_contents = $this->getFunctionMock('App\Modules\Player\Helper\Index', 'file_get_contents');
		$file_get_contents->expects($this->once())->willReturn(false);

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('File get content error with: '.$this->filePath);
		$this->fileUtils->getETag($this->filePath);
	}

	#[RunInSeparateProcess] #[Group('units')]
	public function testGetSite(): void
	{
		$filesize = $this->getFunctionMock('App\Modules\Player\Helper\Index', 'filesize');
		$filesize->expects($this->once())->willReturn(false);

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Filesize error with: '.$this->filePath);
		$this->fileUtils->getFileSize($this->filePath);
	}

	#[RunInSeparateProcess] #[Group('units')]
	public function testCreateSream(): void
	{
		$fopen = $this->getFunctionMock('App\Modules\Player\Helper\Index', 'fopen');
		$fopen->expects($this->once())->willReturn(false);

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Stream  open error with: '.$this->filePath);
		$this->fileUtils->createStream($this->filePath);
	}

}
