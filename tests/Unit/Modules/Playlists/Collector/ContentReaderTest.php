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


namespace Tests\Unit\Modules\Playlists\Collector;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Modules\Playlists\Collector\ContentReader;
use App\Modules\Playlists\Collector\Contracts\ContentReaderInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContentReaderTest extends TestCase
{
	private Filesystem&MockObject $fileSystemMock;
	private Config&MockObject $configMock;

	private ContentReaderInterface $reader;

	/**
	 * @throws Exception|CoreException
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->configMock = $this->createMock(Config::class);
		$this->fileSystemMock = $this->createMock(Filesystem::class);

		$this->configMock->method('getConfigValue')
			->with('path_playlists', 'playlists')
			->willReturn('path/to/playlists');

		$this->reader = new ContentReader($this->configMock, $this->fileSystemMock);
	}

	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testLoadPlaylistItemsReturnsContent(): void
	{
		$playlistId = 123;
		$fileContent = '<items>';

		$this->reader->init($playlistId);

		$this->fileSystemMock
			->method('read')
			->with('path/to/playlists/' . $playlistId . '/items.smil')
			->willReturn($fileContent);


		$result = $this->reader->loadPlaylistItems();

		$this->assertSame($fileContent, $result);
	}

	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testLoadPlaylistItemsReturnsEmptyForInvalidPlaylistId(): void
	{
		$this->reader->init(0);

		$result = $this->reader->loadPlaylistItems();

		$this->assertSame('', $result);
	}

	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testLoadPlaylistPrefetchReturnsContent(): void
	{
		$playlistId = 456;
		$fileContent = '<prefetch>';

		$this->reader->init($playlistId);

		$this->fileSystemMock
			->method('read')
			->with('path/to/playlists/' . $playlistId . '/prefetch.smil')
			->willReturn($fileContent);

		$result = $this->reader->loadPlaylistPrefetch();

		$this->assertSame($fileContent, $result);
	}

	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testLoadPlaylistPrefetchReturnsEmptyForInvalidPlaylistId(): void
	{
		$this->reader->init(0);

		$result = $this->reader->loadPlaylistPrefetch();

		$this->assertSame('', $result);
	}

	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testLoadPlaylistExclusiveReturnsContent(): void
	{
		$playlistId = 789;
		$fileContent = '<exclusive>';

		$this->reader->init($playlistId);

		$this->fileSystemMock
			->method('read')
			->with('path/to/playlists/' . $playlistId . '/exclusive.smil')
			->willReturn($fileContent);

		$result = $this->reader->loadPlaylistExclusive();

		$this->assertSame($fileContent, $result);
	}

	/**
	 * @throws FilesystemException
	 */
	#[Group('units')]
	public function testLoadPlaylistExclusiveReturnsEmptyForInvalidPlaylistId(): void
	{
		$this->reader->init(0);

		$this->assertEmpty($this->reader->loadPlaylistExclusive());
	}


}
