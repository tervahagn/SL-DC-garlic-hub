<?php

namespace Tests\Unit\Modules\Playlists\Helper\ExportSmil\items;

use App\Framework\Core\Config\Config;
use App\Modules\Playlists\Helper\ExportSmil\items\Base;
use App\Modules\Playlists\Helper\ExportSmil\items\Image;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Conditional;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Properties;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Trigger;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
	private Trigger&MockObject $beginMock;
	private Trigger&MockObject $endMock;
	private Conditional&MockObject $conditionalMock;
	private Image $image;

	/**
	 * @throws Exception|\PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$configMock = $this->createMock(Config::class);
		$propertiesMock = $this->createMock(Properties::class);
		$this->beginMock       = $this->createMock(Trigger::class);
		$this->endMock         = $this->createMock(Trigger::class);
		$this->conditionalMock = $this->createMock(Conditional::class);

		$item = ['item_id' => 1, 'item_name' => 'Sample Item', 'item_duration' => 500, 'flags' => 0, 'datasource' => 'file'];
		$this->image = new Image($configMock, $item, $propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock, );
	}

	#[Group('units')]
	public function testGetSmilElementTag(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(false);
		$this->endMock->method('hasTriggers')->willReturn(false);
		$this->conditionalMock->method('determineExprAttribute')->willReturn('');

		$this->image->setLink('/path/to//image.jpg');

		$expected  = Base::TABSTOPS_TAG.'<img xml:id="1" title="Sample Item" region="screen" src="path/to//image.jpg" dur="500s" >'."\n";
		$expected .= Base::TABSTOPS_PARAMETER.'<param name="cacheControl" value="onlyIfCached" />'."\n";
		$expected .= Base::TABSTOPS_TAG.'</img>'."\n";

		$this->assertSame($expected, $this->image->getSmilElementTag());
	}

}
