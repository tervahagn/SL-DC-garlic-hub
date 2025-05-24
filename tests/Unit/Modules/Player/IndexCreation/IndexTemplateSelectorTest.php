<?php

namespace Tests\Unit\Modules\Player\IndexCreation;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\Enums\PlayerModel;
use App\Modules\Player\Enums\TemplateIndexFiles;
use App\Modules\Player\IndexCreation\IndexTemplateSelector;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class IndexTemplateSelectorTest extends TestCase
{
	private IndexTemplateSelector $selector;
	private PlayerEntity $playerEntityMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->selector = new IndexTemplateSelector();
		$this->playerEntityMock = $this->createMock(PlayerEntity::class);
	}

	#[Group('units')]
	public function testSelectReturnsXmp2xxxForIadeaXmp2x00(): void
	{
		$this->playerEntityMock
			->method('getModel')
			->willReturn(PlayerModel::IADEA_XMP2X00);

		$result = $this->selector->select($this->playerEntityMock);

		$this->assertSame(TemplateIndexFiles::XMP2XXX, $result);
	}

	#[Group('units')]
	public function testSelectReturnsXmp2xxxForQbic(): void
	{
		$this->playerEntityMock
			->method('getModel')
			->willReturn(PlayerModel::QBIC);

		$result = $this->selector->select($this->playerEntityMock);

		$this->assertSame(TemplateIndexFiles::XMP2XXX, $result);
	}

	#[Group('units')]
	public function testSelectReturnsGarlicForGarlicWithValidFirmware(): void
	{
		$this->playerEntityMock
			->method('getModel')
			->willReturn(PlayerModel::GARLIC);

		$this->playerEntityMock
			->method('getFirmwareVersion')
			->willReturn('1.0.566');

		$result = $this->selector->select($this->playerEntityMock);

		$this->assertSame(TemplateIndexFiles::GARLIC, $result);
	}

	#[Group('units')]
	public function testSelectReturnsSimpleForGarlicWithInvalidFirmware(): void
	{
		$this->playerEntityMock
			->method('getModel')
			->willReturn(PlayerModel::GARLIC);

		$this->playerEntityMock
			->method('getFirmwareVersion')
			->willReturn('1.0.565');

		$result = $this->selector->select($this->playerEntityMock);

		$this->assertSame(TemplateIndexFiles::SIMPLE, $result);
	}

	#[Group('units')]
	public function testSelectReturnsSimpleForIadeaXmp1x0(): void
	{
		$this->playerEntityMock
			->method('getModel')
			->willReturn(PlayerModel::IADEA_XMP1X0);

		$result = $this->selector->select($this->playerEntityMock);

		$this->assertSame(TemplateIndexFiles::SIMPLE, $result);
	}

	#[Group('units')]
	public function testSelectReturnsSimpleForScreenlite(): void
	{
		$this->playerEntityMock
			->method('getModel')
			->willReturn(PlayerModel::SCREENLITE);

		$result = $this->selector->select($this->playerEntityMock);

		$this->assertSame(TemplateIndexFiles::SIMPLE, $result);
	}
	#[Group('units')]
	public function testSelectReturnsSimpleForUnknownModel(): void
	{
		$this->playerEntityMock
			->method('getModel')
			->willReturn(PlayerModel::UNKNOWN);

		$result = $this->selector->select($this->playerEntityMock);

		$this->assertSame(TemplateIndexFiles::SIMPLE, $result);
	}
}
