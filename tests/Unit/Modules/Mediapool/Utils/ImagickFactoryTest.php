<?php

namespace Tests\Unit\Modules\Mediapool\Utils;

use App\Modules\Mediapool\Utils\ImagickFactory;
use Imagick;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ImagickFactoryTest extends TestCase
{
	#[Group('units')]
	public function testCreateImagickReturnsImagickInstance()
	{
		$factory = new ImagickFactory();
		$imagick = $factory->createImagick();
		$this->assertInstanceOf(Imagick::class, $imagick);
	}

	#[Group('units')]
	public function testCreateImagickCreatesNewInstanceEachTime()
	{
		$factory = new ImagickFactory();
		$imagick1 = $factory->createImagick();
		$imagick2 = $factory->createImagick();
		$this->assertNotSame($imagick1, $imagick2);
	}
}
