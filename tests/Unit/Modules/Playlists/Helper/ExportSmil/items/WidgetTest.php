<?php

namespace Tests\Unit\Modules\Playlists\Helper\ExportSmil\items;

use App\Framework\Core\Config\Config;
use App\Modules\Playlists\Helper\ExportSmil\items\Base;
use App\Modules\Playlists\Helper\ExportSmil\items\Widget;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Conditional;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Properties;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Trigger;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class WidgetTest extends TestCase
{
	private readonly Config $configMock;
	private readonly Properties $propertiesMock;
	private readonly Trigger $beginMock;
	private readonly Trigger $endMock;
	private readonly Conditional $conditionalMock;

	private Widget $widget;

	/**
	 * @throws Exception|\PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$this->configMock      = $this->createMock(Config::class);
		$this->propertiesMock  = $this->createMock(Properties::class);
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

		$item = ['item_id' => 1, 'item_name' => 'Sample widget', 'item_duration' => 50, 'flags' => 0, 'datasource' => 'file'];
		$this->widget = new Widget($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock);

		$this->widget->setLink('/path/to/widget.wgt');

		$expected  = Base::TABSTOPS_TAG.'<ref xml:id="1" title="Sample widget" region="screen" src="path/to/widget.wgt" dur="50s"  type="application/widget">'."\n";
		$expected .= Base::TABSTOPS_PARAMETER.'<param name="cacheControl" value="onlyIfCached" />'."\n";
		$expected .= Base::TABSTOPS_TAG.'</ref>'."\n";

		$this->assertSame($expected, $this->widget->getSmilElementTag());
	}

	#[Group('units')]
	public function testGetSmilElementTagWithContentData(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(false);
		$this->endMock->method('hasTriggers')->willReturn(false);
		$this->conditionalMock->method('determineExprAttribute')->willReturn('');
		$this->propertiesMock->method('getVolume')->willReturn('soundLevel="100"');

		$contentData = ['key' => 'value', 'key2' => 'value2'];
		$item = ['item_id' => 1, 'item_name' => 'Sample widget', 'item_duration' => 50, 'flags' => 0, 'datasource' => 'file', 'content_data' => $contentData];
		$this->widget = new Widget($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock);

		$this->widget->setLink('/path/to/widget.wgt');

		$expected  = Base::TABSTOPS_TAG.'<ref xml:id="1" title="Sample widget" region="screen" src="path/to/widget.wgt" dur="50s"  type="application/widget">'."\n";
		$expected .= Base::TABSTOPS_PARAMETER.'<param name="cacheControl" value="onlyIfCached" />'."\n";
		$expected .= Base::TABSTOPS_PARAMETER.'<param name="key" value="value" />'."\n";
		$expected .= Base::TABSTOPS_PARAMETER.'<param name="key2" value="value2" />'."\n";
		$expected .= Base::TABSTOPS_TAG.'</ref>'."\n";

		$this->assertSame($expected, $this->widget->getSmilElementTag());
	}

	#[Group('units')]
	public function testGetSmilElementTagWithContentDataFails(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(false);
		$this->endMock->method('hasTriggers')->willReturn(false);
		$this->conditionalMock->method('determineExprAttribute')->willReturn('');
		$this->propertiesMock->method('getVolume')->willReturn('soundLevel="100"');

		$contentData = 'stuff';
		$item = ['item_id' => 1, 'item_name' => 'Sample widget', 'item_duration' => 50, 'flags' => 0, 'datasource' => 'file', 'content_data' => $contentData];
		$this->widget = new Widget($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock);

		$this->widget->setLink('/path/to/widget.wgt');

		$expected  = Base::TABSTOPS_TAG.'<ref xml:id="1" title="Sample widget" region="screen" src="path/to/widget.wgt" dur="50s"  type="application/widget">'."\n";
		$expected .= Base::TABSTOPS_PARAMETER.'<param name="cacheControl" value="onlyIfCached" />'."\n";
		$expected .= Base::TABSTOPS_TAG.'</ref>'."\n";

		$this->assertSame($expected, $this->widget->getSmilElementTag());
	}

}
