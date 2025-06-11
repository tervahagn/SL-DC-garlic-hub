<?php

namespace Tests\Unit\Modules\Playlists\Collector;

use App\Modules\Playlists\Collector\SimplePlaylistStructureFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class SimplePlaylistStructureFactoryTest extends TestCase
{
	#[Group('units')]
	public function testWork(): void
	{
		$items     = 'heidewitzka';
		$prefetch  = 'Herr';
		$exclusive = 'Kapitän';

		$factory = new SimplePlaylistStructureFactory();
		$entity = $factory->create($items, $prefetch, $exclusive);

		$this->assertSame($items, $entity->getItems());
		$this->assertSame($prefetch, $entity->getPrefetch());
		$this->assertSame($exclusive, $entity->getExclusive());
	}

}
