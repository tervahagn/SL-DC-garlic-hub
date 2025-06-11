<?php

namespace Tests\Unit\Modules\Playlists\Helper\ExportSmil\Utils;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Properties;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PropertiesTest extends TestCase
{
	private Config&MockObject $configMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->configMock = $this->createMock(Config::class);
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testDefaultValues()
	{
		$this->configMock->expects($this->exactly(3))->method('getConfigValue')
			->willReturnMap([
				['fit', 'playlists', 'Defaults', 'meetBest'],
				['media_align', 'playlists', 'Defaults', 'center'],
				['volume', 'playlists', 'Defaults', '100']
			]);

		$properties = new Properties($this->configMock, []);

		$this->assertSame('fit="meetBest" ', $properties->getFit());
		$this->assertSame('mediaAlign="center" ', $properties->getMediaAlign());
		$this->assertSame('soundLevel="100" ', $properties->getVolume());
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testParameterValues()
	{
		$properties = new Properties($this->configMock, ['fit' => 'slice', 'media_align' => 'midRight', 'volume' => '56']);

		$this->assertSame('fit="slice" ', $properties->getFit());
		$this->assertSame('mediaAlign="midRight" ', $properties->getMediaAlign());
		$this->assertSame('soundLevel="56" ', $properties->getVolume());
	}

	/**
	 * @throws CoreException
	 */
	#[Group('units')]
	public function testNonsenseValues()
	{
		$this->configMock->expects($this->once())->method('getConfigValue')
			->with('volume', 'playlists', 'Defaults')
			->willReturn('100');
		$properties = new Properties($this->configMock, ['fit' => 'Bämm', 'media_align' => 'Bämm']);

		$this->assertEmpty($properties->getFit());
		$this->assertEmpty($properties->getMediaAlign());
		$this->assertSame('soundLevel="100" ', $properties->getVolume());
	}

}
