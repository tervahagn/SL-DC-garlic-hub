<?php

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
	private Config&MockObject $configMock;
	private UserAgentHandler&MockObject $userAgentHandlerMock;
	private PlayerEntityFactory $factory;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->configMock = $this->createMock(Config::class);
		$this->userAgentHandlerMock = $this->createMock(UserAgentHandler::class);

		$this->factory = new PlayerEntityFactory($this->configMock);
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
		$this->assertInstanceOf(PlayerEntity::class, $playerEntity);

		$this->assertSame(1,$playerEntity->getPlayerId());
	}

}
