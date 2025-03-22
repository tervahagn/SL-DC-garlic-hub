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

use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\Utils\MimeTypeDetector;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;


class MimeTypeDetectorTest extends TestCase
{
	private MimeTypeDetector $mimeTypeDetector;
	private string $baseDirectory;

	protected function setUp(): void
	{
		$this->baseDirectory = getenv('TEST_BASE_DIR') . '/resources/tmp';
		$this->mimeTypeDetector = new MimeTypeDetector();
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testDetectFromFileReturnsCorrectMimeType()
	{
		$filePath = $this->baseDirectory  . '/testfile.txt';
		file_put_contents($filePath, 'test content');
		$mimeType = $this->mimeTypeDetector->detectFromFile($filePath);
		$this->assertEquals('text/plain', $mimeType);
		unlink($filePath);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testDetectFromFileThrowsExceptionForNonExistentFile()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->mimeTypeDetector->detectFromFile('non_existent_file.txt');
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testDetectFromFileReturnsWidgetMimeTypeForWgtExtension()
	{
		$filePath = $this->baseDirectory  . '/testfile.wgt';
		file_put_contents($filePath, 'test content');
		$mimeType = $this->mimeTypeDetector->detectFromFile($filePath);
		$this->assertEquals('application/widget', $mimeType);
		unlink($filePath);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testDetectFromStreamReturnsCorrectMimeType()
	{
		$stream = fopen('php://memory', 'r+');
		fwrite($stream, 'test content');
		rewind($stream);
		$mimeType = $this->mimeTypeDetector->detectFromStream($stream);
		$this->assertEquals('text/plain', $mimeType);
		fclose($stream);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testDetectFromStreamThrowsExceptionForInvalidStream()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->mimeTypeDetector->detectFromStream('not a stream');
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testDetectFromStreamThrowsModuleExceptionForUnreadableStream()
	{
		$stream = fopen('php://memory', 'r');
		fclose($stream);
		$this->expectException(InvalidArgumentException::class);
		$this->mimeTypeDetector->detectFromStream($stream);
	}
}