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

namespace Tests\Unit\Modules\Mediapool\Utils;

use App\Modules\Mediapool\Utils\ZipFilesystemFactory;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ZipFilesystemFactoryTest extends TestCase
{
	private string $baseDirectory;
	protected function setUp(): void
	{
		parent::setUp();
		$this->baseDirectory = getenv('TEST_BASE_DIR') . '/resources/widgets';
	}

	#[Group('units')]
	public function testCreateReturnsFilesystemInstance(): void
	{
		$zipPath    = $this->baseDirectory.'/widget.wgt';
		$factory    = new ZipFilesystemFactory();
		$filesystem = $factory->create($zipPath);

		// @phpstan-ignore-next-line
		$this->assertInstanceOf(Filesystem::class, $filesystem);
	}
}
