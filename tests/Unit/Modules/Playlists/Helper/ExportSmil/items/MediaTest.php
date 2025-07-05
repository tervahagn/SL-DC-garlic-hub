<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);

namespace Tests\Unit\Modules\Playlists\Helper\ExportSmil\items;

use App\Framework\Core\Config\Config;
use App\Modules\Playlists\Helper\ExportSmil\items\Base;
use App\Modules\Playlists\Helper\ExportSmil\items\Media;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Conditional;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Properties;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Trigger;
use App\Modules\Playlists\Helper\ItemFlags;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConcreteMedia extends Media
{
	public function getSmilElementTag(): string
	{
		return 'mediaTag';
	}

	public function testCollectAttributes(): string
	{
		return $this->collectMediaAttributes();
	}

	public function testCollectParameters(): string
	{
		return $this->collectParameters();
	}

	public function testLoggable(): string
	{
		return $this->checkLoggable();
	}

}

class MediaTest extends TestCase
{
	private Config&MockObject $configMock;
	private Trigger&MockObject $beginMock;
	private Trigger&MockObject $endMock;
	private Conditional&MockObject $conditionalMock;
	private Properties&MockObject $propertiesMock;
	private ConcreteMedia $concreteMedia;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->configMock = $this->createMock(Config::class);
		$this->propertiesMock = $this->createMock(Properties::class);
		$this->beginMock = $this->createMock(Trigger::class);
		$this->endMock = $this->createMock(Trigger::class);
		$this->conditionalMock = $this->createMock(Conditional::class);

	}

	#[Group('units')]
	public function testGetPrefetchTag(): void
	{
		$item = ['item_id' => 1, 'item_name' => 'Example Title', 'item_duration' => 1000, 'mimetype' => 'video/mp4'];
		$this->concreteMedia = new ConcreteMedia($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock,);

		$this->concreteMedia->setLink('/example/path');

		$result = $this->concreteMedia->getPrefetchTag();
		$this->assertSame(Base::TABSTOPS_TAG . '<prefetch src="example/path" />' . "\n", $result);
	}

	#[Group('units')]
	public function testGetPrefetchTagWithWebsite(): void
	{
		$item = ['item_id' => 1, 'item_name' => 'Example Title', 'item_duration' => 1000, 'mimetype' => 'text/html'];
		$this->concreteMedia = new ConcreteMedia($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock,);

		$this->concreteMedia->setLink('/example/path');

		$this->assertEmpty($this->concreteMedia->getPrefetchTag());
	}

	#[Group('units')]
	public function testCollectMediaAttributes(): void
	{
		$item = ['item_id' => 1, 'item_name' => 'Sample Item', 'item_duration' => 5000];
		$this->propertiesMock->method('getFit')->willReturn('fit="fill" ');
		$this->propertiesMock->method('getMediaAlign')->willReturn('align="center" ');
		$this->concreteMedia = new ConcreteMedia($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock,);

		$this->concreteMedia->setLink('/test/path');

		$result = $this->concreteMedia->testCollectAttributes();

		$this->assertSame('xml:id="1" title="Sample Item" region="screen" src="test/path" dur="5000s" fit="fill" align="center" ', $result);
	}

	#[Group('units')]
	public function testCollectMediaAttributesEmptyProperties(): void
	{
		$item = ['item_id' => 1, 'item_name' => 'Sample Item', 'item_duration' => 5000];
		$this->propertiesMock->method('getFit')->willReturn('');
		$this->propertiesMock->method('getMediaAlign')->willReturn('');
		$this->concreteMedia = new ConcreteMedia($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock,);

		$this->concreteMedia->setLink('/empty/path');

		$result = $this->concreteMedia->testCollectAttributes();

		$this->assertSame('xml:id="1" title="Sample Item" region="screen" src="empty/path" dur="5000s" ', $result);
	}

	#[Group('units')]
	public function testCollectParametersWithFileDatasource(): void
	{
		$item = ['item_id' => 1, 'datasource' => 'file', 'flags' => 0];
		$this->concreteMedia = new ConcreteMedia($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock,);

		$result = $this->concreteMedia->testCollectParameters();

		$this->assertSame(Base::TABSTOPS_PARAMETER . '<param name="cacheControl" value="onlyIfCached" />' . "\n", $result);
	}

	#[Group('units')]
	public function testCollectParametersWithNonFileDatasource(): void
	{
		$item = ['item_id' => 1, 'smil_playlist_item_id' => 123, 'datasource' => 'stream', 'flags' => 0];
		$this->concreteMedia = new ConcreteMedia($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock,);

		$result = $this->concreteMedia->testCollectParameters();

		$this->assertSame('', $result);
	}

	#[Group('units')]
	public function testCollectWithLoggableFlag(): void
	{
		$item = ['item_id' => 123, 'flags' => ItemFlags::loggable->value];
		$this->concreteMedia = new ConcreteMedia($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock,);

		$result = $this->concreteMedia->testLoggable();

		$this->assertSame(Base::TABSTOPS_PARAMETER . '<param name="logContentId" value="123" />' . "\n", $result);
	}

	#[Group('units')]
	public function testCollectWithOutLoggableFlag(): void
	{
		$item = ['item_id' => 123, 'flags' => 0];
		$this->concreteMedia = new ConcreteMedia($this->configMock, $item, $this->propertiesMock, $this->beginMock, $this->endMock, $this->conditionalMock,);

		$this->assertEmpty($this->concreteMedia->testLoggable());
	}


}
