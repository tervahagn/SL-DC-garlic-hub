<?php

namespace Tests\Unit\Modules\Playlists\Helper\ExportSmil\items;

use App\Framework\Core\Config\Config;
use App\Modules\Playlists\Helper\ExportSmil\items\Base;
use App\Modules\Playlists\Helper\ExportSmil\items\Video;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Conditional;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Properties;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Trigger;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VideoTest extends TestCase
{
	private Config&MockObject $configMock;
	private Properties&MockObject $propertiesMock;
	private Trigger&MockObject $beginMock;
	private Trigger&MockObject $endMock;
	private Conditional&MockObject $conditionalMock;
	private Video $video;

	/**
	 * @throws Exception|\PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$this->configMock = $this->createMock(Config::class);
		$this->propertiesMock = $this->createMock(Properties::class);
		$this->beginMock       = $this->createMock(Trigger::class);
		$this->endMock         = $this->createMock(Trigger::class);
		$this->conditionalMock = $this->createMock(Conditional::class);

	}

	#[Group('units')]
	public function testGetSmilElementTag(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(false);
		$this->endMock->method('hasTriggers')->willReturn(false);
		$this->conditionalMock->method('determineExprAttribute')->willReturn('');
		$this->propertiesMock->method('getVolume')->willReturn('soundLevel="100"');

		$item = ['item_id' => 1, 'item_name' => 'Sample Item', 'item_duration' => 500, 'flags' => 0, 'datasource' => 'file'];
		$this->video = new Video($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock);

		$this->video->setLink('/path/to/video.webm');

		$expected  = Base::TABSTOPS_TAG.'<video xml:id="1" title="Sample Item" region="screen" src="path/to/video.webm" dur="500s" soundLevel="100">'."\n";
		$expected .= Base::TABSTOPS_PARAMETER.'<param name="cacheControl" value="onlyIfCached" />'."\n";
		$expected .= Base::TABSTOPS_TAG.'</video>'."\n";

		$this->assertSame($expected, $this->video->getSmilElementTag());
	}

	#[Group('units')]
	public function testGetSmilElementTagAsStream(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(false);
		$this->endMock->method('hasTriggers')->willReturn(false);
		$this->conditionalMock->method('determineExprAttribute')->willReturn('');
		$this->propertiesMock->method('getVolume')->willReturn('soundLevel="100"');

		$item = ['item_id' => 1, 'item_name' => 'Sample Item', 'item_duration' => 500, 'flags' => 0, 'datasource' => 'stream'];
		$this->video = new Video($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock);

		$this->video->setLink('/path/to/video.webm');

		$expected  = Base::TABSTOPS_TAG.'<video xml:id="1" title="Sample Item" region="screen" src="path/to/video.webm" dur="500s" soundLevel="100">'."\n";
		$expected .= Base::TABSTOPS_PARAMETER.'<param name="stream" value="true" />'."\n";
		$expected .= Base::TABSTOPS_TAG.'</video>'."\n";

		$this->assertSame($expected, $this->video->getSmilElementTag());
	}
}
