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
declare(strict_types=1);


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
		parent::setUp();
		$this->fileUtils = new FileUtils();
		$baseDir = getenv('TEST_BASE_DIR') . '/resources/tmp';
		$this->filePath = $baseDir.'/test.bin';
		file_put_contents($this->filePath, "\xDE\xAD\xBE\xEF");
	}

	protected function tearDown(): void
	{
		parent::tearDown();
		unlink($this->filePath);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testGetFileMethods(): void
	{
		static::assertSame(filemtime($this->filePath), $this->fileUtils->getFileMTime($this->filePath));
		static::assertSame(4, $this->fileUtils->getFileSize($this->filePath));
		static::assertSame('2f249230a8e7c2bf6005ccd2679259ec', $this->fileUtils->getETag($this->filePath));

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
