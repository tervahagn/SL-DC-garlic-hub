<?php

namespace Tests\Unit\Modules\Player\Helper\Index;

use App\Modules\Player\Helper\Index\FileUtils;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Stream;

class FileUtilsTest extends TestCase
{
	private readonly FileUtils $fileUtils;

	#[Group('units')]
	public function testGetFileMethods(): void
	{
		$this->fileUtils = new FileUtils();

		$baseDir = getenv('TEST_BASE_DIR') . '/resources/tmp';
		$filePath = $baseDir.'/test.bin';
		file_put_contents($filePath, "\xDE\xAD\xBE\xEF");
		$this->assertSame(filemtime($filePath), $this->fileUtils->getFileMTime($filePath));
		$this->assertSame(4, $this->fileUtils->getFileSize($filePath));
		$this->assertSame('2f249230a8e7c2bf6005ccd2679259ec', $this->fileUtils->getETag($filePath));



		$this->assertInstanceOf(Stream::class, $this->fileUtils->createStream($filePath));
		unlink($filePath);
	}
}
