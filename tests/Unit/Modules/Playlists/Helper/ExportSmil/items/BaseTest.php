<?php

namespace Tests\Unit\Modules\Playlists\Helper\ExportSmil\items;

use App\Framework\Core\Config\Config;
use App\Modules\Playlists\Helper\ExportSmil\items\Base;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Conditional;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Properties;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Trigger;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

cLass ConcreteBase extends Base
{

	public function getPrefetchTag(): string
	{
		return 'prefetch';
	}

	public function getSmilElementTag(): string
	{
		return 'smilElementTag';
	}

	public function testTrigger(): string
	{
		return $this->determineBeginEndTrigger();
	}

	public function testCollectAttributes(): string
	{
		return $this->collectAttributes();
	}

	public function testInsertXmlId(): string
	{
		return $this->insertXmlId();
	}

	public function testEncodeTitle(): string
	{
		return $this->encodeItemNameForTitleTag();
	}

	public function testDetermineDuration(): string
	{
		return $this->determineDuration();
	}
}

class BaseTest extends TestCase
{
	private readonly Config $configMock;
	private readonly Trigger $beginMock;
	private readonly Trigger $endMock;
	private readonly Conditional $conditionalMock;
	private readonly Properties $propertiesMock;
	private ConcreteBase $concreteBase;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->configMock      = $this->createMock(Config::class);
		$this->propertiesMock  = $this->createMock(Properties::class);
		$this->beginMock       = $this->createMock(Trigger::class);
		$this->endMock         = $this->createMock(Trigger::class);
		$this->conditionalMock = $this->createMock(Conditional::class);

		$item = ['item_id' => 1, 'item_name' => 'Example Title', 'item_duration' => 1000];
		$this->concreteBase = new ConcreteBase($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock, );
	}

	#[Group('units')]
	public function testDetermineBeginEndTrigger(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(true);
		$this->beginMock->method('determineTrigger')->willReturn('trigger_begin');
		$this->endMock->method('hasTriggers')->willReturn(true);
		$this->endMock->method('determineTrigger')->willReturn('trigger_end');

		$result = $this->concreteBase->testTrigger();
		$this->assertSame('begin="trigger_begin" end="trigger_end" ', $result);
	}

	#[Group('units')]
	public function testDetermineBeginEndTriggerBeginOnly(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(true);
		$this->beginMock->method('determineTrigger')->willReturn('trigger_begin');
		$this->endMock->method('hasTriggers')->willReturn(false);

		$result = $this->concreteBase->testTrigger();
		$this->assertSame('begin="trigger_begin" ', $result);
	}

	#[Group('units')]
	public function testDetermineBeginEndTriggerEndOnly(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(false);
		$this->endMock->method('hasTriggers')->willReturn(true);
		$this->endMock->method('determineTrigger')->willReturn('trigger_end');

		$result = $this->concreteBase->testTrigger();
		$this->assertSame('end="trigger_end" ', $result);
	}

	#[Group('units')]
	public function testCollectAttributes(): void
	{
		$this->conditionalMock->method('determineExprAttribute')->willReturn('expr="" ');

		$result = $this->concreteBase->testCollectAttributes();
		$this->assertSame('xml:id="1" expr="" title="Example Title" ', $result);
	}

	#[Group('units')]
	public function testInsertXmlIdForNonMasterPlaylist(): void
	{
		$result = $this->concreteBase->testInsertXmlId();
		$this->assertSame('xml:id="1" ', $result);
	}

	#[Group('units')]
	public function testInsertXmlIdForMasterPlaylist(): void
	{
		$this->concreteBase->setIsMasterPlaylist(true);
		$result = $this->concreteBase->testInsertXmlId();
		$this->assertSame('xml:id="m1" ', $result);
	}

	#[Group('units')]
	public function testGetExclusiveWithTriggers(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(true);
		$this->beginMock->method('determineTrigger')->willReturn('trigger_begin');
		$this->concreteBase->setIsMasterPlaylist(true);

		$result = $this->concreteBase->getExclusive();

		$this->assertStringContainsString('<priorityClass>', $result);
		$this->assertStringContainsString('smilElementTag', $result);
		$this->assertStringContainsString('</priorityClass>', $result);
	}

	#[Group('units')]
	public function testGetExclusiveWithoutTriggers(): void
	{
		$this->beginMock->method('hasTriggers')->willReturn(false);

		$result = $this->concreteBase->getExclusive();

		$this->assertSame('', $result);
	}

	#[Group('units')]
	public function testEncodeItemNameForTitleTag(): void
	{
		$result = $this->concreteBase->testEncodeTitle();
		$this->assertStringContainsString('title="Example Title" ', $result);
	}

	#[Group('units')]
	public function testEncodeItemNameForTitleTagWithSpecialCharacters(): void
	{
		$item = ['item_id' => 1, 'item_name' => 'Special <Title> & $^', 'item_duration' => 1000];
		$this->concreteBase = new ConcreteBase($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock);

		$result = $this->concreteBase->testEncodeTitle();
		$this->assertStringContainsString('title="Special &lt;Title&gt; &amp; $^" ', $result);
	}

	#[Group('units')]
	public function testEncodeItemNameForTitleTagWithAmpersand(): void
	{
		$item = ['item_id' => 1, 'item_name' => 'Title & Subtitle', 'item_duration' => 1000];
		$this->concreteBase = new ConcreteBase($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock);

		$result = $this->concreteBase->testEncodeTitle();
		$this->assertStringContainsString('title="Title &amp; Subtitle" ', $result);
	}

	#[Group('units')]
	public function testDetermineDuration(): void
	{
		$result = $this->concreteBase->testDetermineDuration();
		$this->assertStringContainsString('dur="1000s"', $result);
	}

	#[Group('units')]
	public function testEncodeItemNameForTitleTagWithNullName(): void
	{
		$item = ['item_id' => 1, 'item_name' => null, 'item_duration' => 0];
		$this->concreteBase = new ConcreteBase($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock);

		$result = $this->concreteBase->testDetermineDuration();
		$this->assertEmpty($result);
	}
}
