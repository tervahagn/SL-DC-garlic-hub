<?php

namespace Tests\Unit\Modules\Playlists\Collector;

use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Collector\ExternalContentReader;
use GuzzleHttp\Client;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ExternalContentReaderTest extends TestCase
{
	private readonly FileSystem $fileSystemMock;
	private readonly Client $clientMock;
	private readonly ExternalContentReader $reader;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->fileSystemMock = $this->createMock(Filesystem::class);
		$this->clientMock = $this->createMock(Client::class);

		$this->reader = new ExternalContentReader($this->fileSystemMock, $this->clientMock, 'path/to/cache');
	}

	#[Group('units')]
	public function testLoadPlaylistItemsWithoutCachedFile(): void
	{
		$this->fileSystemMock->expects($this->once())
			->method('fileExists')
			->with('path/to/cache/' . md5('playlist/url') . '.smil')
			->willReturn(false);

		$responseMock = $this->createMock(ResponseInterface::class);
		$this->clientMock->expects($this->once())
			->method('head')
			->with('playlist/url')
			->willReturn($responseMock);
		;

		$responseMock->expects($this->once())->method('getStatusCode')->willReturn(200);

		$this->clientMock
			->expects($this->once())
			->method('get')
			->with('playlist/url', ['sink' => 'path/to/cache/' . md5('playlist/url') . '.smil']);

		$this->fileSystemMock
			->expects($this->once())
			->method('read')
			->with('path/to/cache/' . md5('playlist/url') . '.smil')
			->willReturn('file_content');

		$this->reader->init('playlist/url');
		$this->assertSame('file_content', $this->reader->loadPlaylistItems());
	}

	#[Group('units')]
	public function testLoadPlaylistItemsWithExistingCachedFile(): void
	{
		$this->fileSystemMock
			->expects($this->once())
			->method('fileExists')
			->with('path/to/cache/' . md5('playlist/url') . '.smil')
			->willReturn(true);

		$this->fileSystemMock
			->expects($this->once())
			->method('read')
			->with('path/to/cache/' . md5('playlist/url') . '.smil')
			->willReturn('existing_file_content');

/*		$responseMock = $this->createMock(ResponseInterface::class);
		$this->clientMock->expects($this->once())
			->method('head')
			->with('playlist/url')
			->willReturn($responseMock);

		$responseMock->expects($this->once())->method('getStatusCode')->willReturn(200);

		$responseMock->expects($this->exactly(2))->method('getHeaderLine')
			->willReturnMap([
				['Last-Modified', '14'],
				['Content-Length', '114'],
			]);
		$this->fileSystemMock->method('lastModified')->willReturn(12);
		$this->fileSystemMock->method('fileSize')->willReturn(112);

*/

		$this->reader->init('playlist/url');
		$this->assertSame('existing_file_content', $this->reader->loadPlaylistItems());
	}

	#[Group('units')]
	public function testLoadPlaylistItemsWhenStatusCodeNot200(): void
	{
		$this->fileSystemMock
			->expects($this->once())
			->method('fileExists')
			->with('path/to/cache/' . md5('playlist/url') . '.smil')
			->willReturn(false);


		$responseMock = $this->createMock(ResponseInterface::class);
		$this->clientMock->expects($this->once())
			->method('head')
			->with('playlist/url')
			->willReturn($responseMock);
		;

		$responseMock->method('getStatusCode')->willReturn(300);

		$this->expectException(ModuleException::class);

		$this->reader->init('playlist/url');
		$this->reader->loadPlaylistItems();
	}

}
