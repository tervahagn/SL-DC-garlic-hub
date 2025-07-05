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

namespace Tests\Unit\Modules\Player\Entities;

use App\Framework\Core\Config\Config;
use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\Entities\PlayerEntityFactory;
use App\Modules\Player\Enums\PlayerModel;
use App\Modules\Player\IndexCreation\UserAgentHandler;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PlayerEntityFactoryTest extends TestCase
{
	private UserAgentHandler&MockObject $userAgentHandlerMock;
	private PlayerEntityFactory $factory;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$configMock = $this->createMock(Config::class);
		$this->userAgentHandlerMock = $this->createMock(UserAgentHandler::class);

		$this->factory = new PlayerEntityFactory($configMock);
	}

	#[Group('units')]
	public function testCreateReturnsPlayerEntityInstance(): void
	{
		$data = ['key' => 'value'];

		$this->userAgentHandlerMock->method('getModel')->willReturn(PlayerModel::GARLIC);
		$this->userAgentHandlerMock->method('getUuid')->willReturn('uuid');
		$this->userAgentHandlerMock->method('getFirmware')->willReturn('firmware');
		$this->userAgentHandlerMock->method('getName')->willReturn('name');

		$playerEntity = $this->factory->create($data, $this->userAgentHandlerMock);
		// @phpstan-ignore-next-line
		static::assertInstanceOf(PlayerEntity::class, $playerEntity);

		static::assertSame(1,$playerEntity->getPlayerId());
	}

}
