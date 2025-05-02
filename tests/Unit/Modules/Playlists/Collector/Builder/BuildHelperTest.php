<?php

namespace Tests\Unit\Modules\Playlists\Collector\Builder;

use App\Modules\Playlists\Collector\Builder\BuildHelper;
use App\Modules\Playlists\Collector\Contracts\ContentReaderInterface;
use App\Modules\Playlists\Collector\Contracts\ExternalContentReaderInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class BuildHelperTest extends TestCase
{
	private ContentReaderInterface $contentReaderMock;
	private ExternalContentReaderInterface $externalContentReaderMock;
	private LoggerInterface $loggerMock;
	private BuildHelper $buildHelper;


	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->contentReaderMock = $this->createMock(ContentReaderInterface::class);
		$this->externalContentReaderMock = $this->createMock(ExternalContentReaderInterface::class);
		$this->loggerMock = $this->createMock(LoggerInterface::class);

		$this->buildHelper = new BuildHelper($this->contentReaderMock,	$this->externalContentReaderMock, $this->loggerMock);
	}

	#[Group('units')]
	public function testCollectItemsWithNoPlaceholders(): void
	{
		$playlistId = 1;
		$playlistContent = "Static content with no placeholders.";

		$this->contentReaderMock->method('init')
			->with($playlistId)
			->willReturn($this->contentReaderMock);

		$this->contentReaderMock->method('loadPlaylistItems')
			->willReturn($playlistContent);

		$result = $this->buildHelper->collectItems($playlistId);

		$this->assertSame($playlistContent, $result);
	}

	#[Group('units')]
	public function testCollectItemsWithInternalPlaceholders(): void
	{
		$playlistId = 1;
		$playlistContent = "Content before placeholder {ITEMS_2} content after.";
		$subPlaylistContent = "Subplaylist content.";

		$this->contentReaderMock->method('init')
			->willReturnOnConsecutiveCalls(
				$this->contentReaderMock,
				$this->contentReaderMock
			);

		$this->contentReaderMock
			->method('loadPlaylistItems')
			->willReturnOnConsecutiveCalls($playlistContent, $subPlaylistContent);

		$result = $this->buildHelper->collectItems($playlistId);

		$expected = "Content before placeholder \n$subPlaylistContent\n content after.";
		$this->assertSame($expected, $result);
	}


	#[Group('units')]
	public function testCollectItemsWithRecursivePlaceholders(): void
	{
		$playlistId = 1;
		$playlistContent = "Playlist content {ITEMS_2}.";
		$subPlaylistContent1 = "Subplaylist 2 content {ITEMS_3}.";
		$subPlaylistContent2 = "Final resolved content.{ITEMS_4}";
		$subPlaylistContent3 = "";

		$this->contentReaderMock->method('init')
			->willReturnOnConsecutiveCalls(
				$this->contentReaderMock,
				$this->contentReaderMock,
				$this->contentReaderMock
			);

		$this->contentReaderMock->method('loadPlaylistItems')
			->willReturnOnConsecutiveCalls(
				$playlistContent,
				$subPlaylistContent1,
				$subPlaylistContent2,
				$subPlaylistContent3
			);

		$result = $this->buildHelper->collectItems($playlistId);

		$expected = "Playlist content \nSubplaylist 2 content \nFinal resolved content.\n.\n.";
		$this->assertSame($expected, $result);
	}

	
	#[Group('units')]
	public function testCollectItemsWithExternalPlaceholders(): void
	{
		$playlistId = 1;
		$playlistContent = "Some content {ITEMS_0#https://example.com} more content.";
		$externalContent = "External playlist content.";

		$this->contentReaderMock->method('init')
			->with($playlistId)
			->willReturn($this->contentReaderMock);

		$this->contentReaderMock->method('loadPlaylistItems')
			->willReturn($playlistContent);

		$this->externalContentReaderMock->method('init')
			->with('https://example.com')
			->willReturn($this->externalContentReaderMock);

		$this->externalContentReaderMock->method('loadPlaylistItems')
			->willReturn($externalContent);

		$result = $this->buildHelper->collectItems($playlistId);

		$expected = "Some content $externalContent more content.";
		$this->assertSame($expected, $result);
	}

	#[Group('units')]
	public function testCollectItemsOnException(): void
	{
		$playlistId = 1;

		$this->contentReaderMock->method('init')
			->willThrowException(new RuntimeException("Error during collection."));

		$this->loggerMock->expects($this->once())->method('error')
			->with($this->stringContains("Error recurse items"));

		$result = $this->buildHelper->collectItems($playlistId);

		$this->assertSame('', $result);
	}

	#[Group('units')]
	public function testCollectPrefetchesWithNoPlaceholders(): void
	{
		$playlistId = 1;
		$prefetchContent = "Static prefetch with no placeholders.";

		$this->contentReaderMock->method('init')
			->with($playlistId)
			->willReturn($this->contentReaderMock);

		$this->contentReaderMock->method('loadPlaylistPrefetch')
			->willReturn($prefetchContent);

		$result = $this->buildHelper->collectPrefetches($playlistId);

		$this->assertSame($prefetchContent, $result);
	}


	#[Group('units')]
	public function testCollectPrefetchesWithInternalPlaceholders(): void
	{
		$playlistId = 1;
		$prefetchContent = "Prefetch content {PREFETCH_2}.";
		$subPrefetchContent = "Sub-prefetch content.";

		$this->contentReaderMock->method('init')
			->willReturnOnConsecutiveCalls(
				$this->contentReaderMock,
				$this->contentReaderMock
			);

		$this->contentReaderMock->method('loadPlaylistPrefetch')
			->willReturnOnConsecutiveCalls($prefetchContent, $subPrefetchContent);

		$result = $this->buildHelper->collectPrefetches($playlistId);

		$expected = "Prefetch content $subPrefetchContent.";
		$this->assertSame($expected, $result);
	}

	#[Group('units')]
	public function testCollectPrefetchesOnException(): void
	{
		$playlistId = 1;

		$this->contentReaderMock->method('init')
			->willThrowException(new RuntimeException("Error during prefetch collection."));

		$this->loggerMock->expects($this->once())
			->method('error')
			->with(
				$this->stringContains("Error recurse prefetches")
			);

		$result = $this->buildHelper->collectPrefetches($playlistId);

		$this->assertSame('', $result);
	}

	#[Group('units')]
	public function testCollectExclusivesWithNoPlaceholders(): void
	{
		$playlistId = 1;
		$exclusiveContent = "Exclusive content without placeholders.";

		$this->contentReaderMock->method('init')
			->with($playlistId)
			->willReturn($this->contentReaderMock);

		$this->contentReaderMock->method('loadPlaylistExclusive')
			->willReturn($exclusiveContent);

		$result = $this->buildHelper->collectExclusives($playlistId);

		$this->assertSame($exclusiveContent, $result);
	}

	#[Group('units')]
	public function testCollectExclusivesWithInternalPlaceholders(): void
	{
		$playlistId = 1;
		$exclusiveContent = "Exclusive content with {ITEMS_2}.";
		$subPlaylistContent = "Sub exclusive content.";

		$this->contentReaderMock->expects($this->exactly(3))
			->method('init')
			->willReturn($this->contentReaderMock);

		$this->contentReaderMock->method('loadPlaylistExclusive')
			->willReturnOnConsecutiveCalls(
				$exclusiveContent,
				''
			);

		$this->contentReaderMock->method('loadPlaylistItems')
			->willReturn($subPlaylistContent);

		$result = $this->buildHelper->collectExclusives($playlistId);

		$expected = "Exclusive content with $subPlaylistContent.";
		$this->assertSame($expected, $result);
	}

	#[Group('units')]
	public function testCollectExclusivesWithRecursivePlaceholders(): void
	{
		$playlistId = 1;
		$exclusiveContent = "Exclusive content {ITEMS_2}.";
		$subPlaylistContent1 = "Subcontent 2 {ITEMS_3}.";
		$subPlaylistContent2 = "Resolved content.";

		$this->contentReaderMock->expects($this->exactly(5))
			->method('init')
			->willReturn($this->contentReaderMock);

		$this->contentReaderMock->method('loadPlaylistExclusive')
			->willReturnOnConsecutiveCalls(
				$exclusiveContent,
				'',
				''
			);

		$this->contentReaderMock
			->method('loadPlaylistItems')
			->willReturnOnConsecutiveCalls($subPlaylistContent1, $subPlaylistContent2);

		$result = $this->buildHelper->collectExclusives($playlistId);

		$expected = "Exclusive content Subcontent 2 Resolved content...";
		$this->assertSame($expected, $result);
	}

	#[Group('units')]
	public function testCollectExclusivesOnException(): void
	{
		$playlistId = 1;

		$this->contentReaderMock
			->method('init')
			->willThrowException(new RuntimeException("Error during exclusive collection."));

		$this->loggerMock
			->expects($this->once())
			->method('error')
			->with(
				$this->stringContains("Error recurse exclusive")
			);

		$result = $this->buildHelper->collectExclusives($playlistId);

		$this->assertSame('', $result);
	}

}




