<?php

namespace Tests\Unit\Modules\Playlists\Helper\ExportSmil\items;

use App\Framework\Core\Config\Config;
use App\Modules\Playlists\Helper\ExportSmil\items\Base;
use App\Modules\Playlists\Helper\ExportSmil\items\SeqContainer;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Conditional;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Properties;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Trigger;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SeqContainerTest extends TestCase
{
	private readonly Config&MockObject $configMock;
	private readonly Trigger&MockObject $beginMock;
	private readonly Trigger&MockObject $endMock;
	private readonly Conditional&MockObject $conditionalMock;
	private readonly Properties $propertiesMock;
	private SeqContainer $seqContainer;

	public function setUp(): void
	{
		$this->configMock = $this->createMock(Config::class);
		$this->beginMock       = $this->createMock(Trigger::class);
		$this->endMock         = $this->createMock(Trigger::class);
		$this->conditionalMock = $this->createMock(Conditional::class);
		$this->propertiesMock = $this->createMock(Properties::class);
	}


	#[Group('units')]
	public function testGetSmilElementTag(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(false);

		$item = ['item_id' => 1, 'item_name' => 'foo', 'file_resource' => '12345'];

		$this->seqContainer = new SeqContainer(
			$this->configMock,
			$item,
			$this->propertiesMock,
			$this->beginMock,
			$this->endMock,
			$this->conditionalMock
		);

		$expected = Base::TABSTOPS_TAG . '<seq xml:id="1" title="foo" >' . "\n" .
			Base::TABSTOPS_PARAMETER . '{ITEMS_12345}' . "\n" .
			Base::TABSTOPS_TAG . '</seq>' . "\n";

		$this->assertSame($expected, $this->seqContainer->getSmilElementTag());
	}

	#[Group('units')]
	public function testGetSmilElementTagWithTriggers(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(true);

		$this->seqContainer = new SeqContainer(
		$this->configMock,
			[],
			$this->propertiesMock,
			$this->beginMock,
			$this->endMock,
			$this->conditionalMock
		);

		$this->assertSame('', $this->seqContainer->getSmilElementTag());
	}

	#[Group('units')]
	public function testGetElementLinkWithScheduledStartDate(): void
	{
		$item = ['external_link' => 'example.com'];

		$this->seqContainer = new SeqContainer(
			$this->configMock,
			$item,
			$this->propertiesMock,
			$this->beginMock,
			$this->endMock,
			$this->conditionalMock
		);

		$expected = Base::TABSTOPS_TAG . '{ITEMS_0#example.com}' . "\n";

		$this->assertSame($expected, $this->seqContainer->getElementLink());
	}

	#[Group('units')]
	public function testGetPrefetchTag(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(false);

		$item = ['file_resource' => '12345'];

		$this->seqContainer = new SeqContainer(
			$this->configMock,
			$item,
			$this->propertiesMock,
			$this->beginMock,
			$this->endMock,
			$this->conditionalMock
		);

		$expected = Base::TABSTOPS_TAG . '{PREFETCH_12345}' . "\n";

		$this->assertSame($expected, $this->seqContainer->getPrefetchTag());
	}
}
