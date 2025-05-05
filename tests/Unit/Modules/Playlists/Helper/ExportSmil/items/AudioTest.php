<?php

namespace Tests\Unit\Modules\Playlists\Helper\ExportSmil\items;

use App\Framework\Core\Config\Config;
use App\Modules\Playlists\Helper\ExportSmil\items\Audio;
use App\Modules\Playlists\Helper\ExportSmil\items\Base;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Conditional;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Properties;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Trigger;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class AudioTest extends TestCase
{
	private readonly Config $configMock;
	private readonly Properties $propertiesMock;
	private readonly Trigger $beginMock;
	private readonly Trigger $endMock;
	private readonly Conditional $conditionalMock;
	private Audio $audio;

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
		$this->audio = new Audio($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock);

		$this->audio->setLink('/path/to/video.webm');

		$expected  = Base::TABSTOPS_TAG.'<audio xml:id="1" title="Sample Item" region="screen" src="path/to/video.webm" dur="500s" soundLevel="100">'."\n";
		$expected .= Base::TABSTOPS_PARAMETER.'<param name="cacheControl" value="onlyIfCached" />'."\n";
		$expected .= Base::TABSTOPS_TAG.'</audio>'."\n";

		$this->assertSame($expected, $this->audio->getSmilElementTag());
	}
}
