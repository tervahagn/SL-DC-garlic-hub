<?php

namespace Tests\Unit\Modules\Player\IndexCreation;

use App\Framework\Core\Config\Config;
use App\Modules\Player\Enums\PlayerModel;
use App\Modules\Player\IndexCreation\PlayerDetector;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class PlayerDetectorTest extends TestCase
{
	private PlayerDetector $detector;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$configMock = $this->createMock(Config::class);

		$this->detector = new PlayerDetector($configMock);
	}

	#[Group('units')]
	public function testDetectModelIdForIADEAXMP1X0(): void
	{
		$this->detector->detectModelId('XMP-120');
		$this->assertSame(PlayerModel::IADEA_XMP1X0, $this->detector->getModelId());
	}

	#[Group('units')]
	public function testDetectModelIdForIADEAXMP3X0(): void
	{
		$this->detector->detectModelId('XMP-320');
		$this->assertSame(PlayerModel::IADEA_XMP3X0, $this->detector->getModelId());
	}

	#[Group('units')]
	public function testDetectModelIdForIADEAXMP3X50(): void
	{
		$this->detector->detectModelId('XMP-3250');
		$this->assertSame(PlayerModel::IADEA_XMP3X50, $this->detector->getModelId());
	}

	#[Group('units')]
	public function testDetectModelIdForCompatible(): void
	{
		$this->detector->detectModelId('fs5-player');
		$this->assertSame(PlayerModel::COMPATIBLE, $this->detector->getModelId());
	}

	#[Group('units')]
	public function testDetectModelIdForIADEAXMP2X00(): void
	{
		$this->detector->detectModelId('XMP-2200');
		$this->assertSame(PlayerModel::IADEA_XMP2X00, $this->detector->getModelId());
	}

	#[Group('units')]
	public function testDetectModelIdForGarlic(): void
	{
		$this->detector->detectModelId('Garlic');
		$this->assertSame(PlayerModel::GARLIC, $this->detector->getModelId());
	}

	#[Group('units')]
	public function testDetectModelIdForIDS(): void
	{
		$this->detector->detectModelId('IDS-App');
		$this->assertSame(PlayerModel::IDS, $this->detector->getModelId());
	}

	#[Group('units')]
	public function testDetectModelIdForQBIC(): void
	{
		$this->detector->detectModelId('BXP-202');
		$this->assertSame(PlayerModel::QBIC, $this->detector->getModelId());
	}

	#[Group('units')]
	public function testDetectModelIdForScreenlite(): void
	{
		$this->detector->detectModelId('ScreenliteWeb');
		$this->assertSame(PlayerModel::SCREENLITE, $this->detector->getModelId());
	}

	#[Group('units')]
	public function testDetectModelIdForUnknown(): void
	{
		$this->detector->detectModelId('NonExistingModel');
		$this->assertSame(PlayerModel::UNKNOWN, $this->detector->getModelId());
	}


}
