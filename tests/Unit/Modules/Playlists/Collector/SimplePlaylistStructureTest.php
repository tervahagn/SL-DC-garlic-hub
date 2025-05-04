<?php

namespace Tests\Unit\Modules\Playlists\Collector;

use App\Modules\Playlists\Collector\SimplePlaylistStructure;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class SimplePlaylistStructureTest extends TestCase
{

	#[Group('units')]
	public function testValidity(): void
	{
		$items     = 'heidewitzka';
		$prefetch  = 'Herr';
		$exclusive = 'Kapitän';

		$entity = new SimplePlaylistStructure($items, $prefetch, $exclusive);

		$this->assertSame($items, $entity->getItems());
		$this->assertSame($prefetch, $entity->getPrefetch());
		$this->assertSame($exclusive, $entity->getExclusive());;
	}

}
