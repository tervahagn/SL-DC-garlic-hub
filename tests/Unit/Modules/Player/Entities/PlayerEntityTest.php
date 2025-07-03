<?php

namespace Tests\Unit\Modules\Player\Entities;

use App\Framework\Core\Config\Config;
use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\Enums\PlayerModel;
use App\Modules\Player\IndexCreation\UserAgentHandler;
use DateTime;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PlayerEntityTest extends TestCase
{
	private Config&MockObject $configMock;
	private UserAgentHandler&MockObject $userAgentHandlerMock;
	private PlayerEntity $playerEntity;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->configMock = $this->createMock(Config::class);
		$this->userAgentHandlerMock = $this->createMock(UserAgentHandler::class);
	}

	#[Group('units')]
	public function testConstructorWithValidData(): void
	{
		$screenTimes = [
			0 => ['screen1' => ['monday' => 'some time']],
			1 => ['screen2' => []]
		];
		$data = [
			'player_id' => 10,
			'playlist_id' => 20,
			'UID' => 12345,
			'last_access' => '2023-10-10 12:00:00',
			'last_update' => '2023-10-15 12:00:00',
			'last_update_playlist' => '2023-10-18 08:00:00',
			'duration' => 3600,
			'status' => 1,
			'refresh' => 120,
			'licence_id' => 56789,
			'playlist_name' => 'Example Playlist',
			'playlist_mode' => 'loop',
			'multizone' => ['zone1', 'zone2'],
			'location_data' => ['city' => 'New York'],
			'location_longitude' => '-74.0060',
			'location_latitude' => '40.7128',
			'categories' => ['category1', 'category2'],
			'properties' => ['width' => 1920, 'height' => 1080],
			'remote_administration' => ['enabled' => true],
			'screen_times' => $screenTimes
		];

		$this->userAgentHandlerMock->method('getModel')->willReturn(PlayerModel::COMPATIBLE);
		$this->userAgentHandlerMock->method('getUuid')->willReturn('123e4567-e89b-12d3-a456-426614174000');
		$this->userAgentHandlerMock->method('getFirmware')->willReturn('1.0.0');
		$this->userAgentHandlerMock->method('getName')->willReturn('Player One');

		$this->playerEntity = new PlayerEntity($this->configMock, $this->userAgentHandlerMock, $data);

		$this->assertSame(10, $this->playerEntity->getPlayerId());
		$this->assertSame(20, $this->playerEntity->getPlaylistId());
		$this->assertSame(12345, $this->playerEntity->getUID());
		$this->assertEquals(new DateTime('2023-10-10 12:00:00'), $this->playerEntity->getLastAccess());
		$this->assertEquals(new DateTime('2023-10-15 12:00:00'), $this->playerEntity->getLastUpdate());
		$this->assertEquals(new DateTime('2023-10-18 08:00:00'), $this->playerEntity->getLastUpdatePlaylist());
		$this->assertSame(3600, $this->playerEntity->getDuration());
		$this->assertSame(1, $this->playerEntity->getStatus());
		$this->assertSame(120, $this->playerEntity->getRefresh());
		$this->assertSame(56789, $this->playerEntity->getLicenceId());
		$this->assertSame(PlayerModel::COMPATIBLE, $this->playerEntity->getModel());
		$this->assertSame('123e4567-e89b-12d3-a456-426614174000', $this->playerEntity->getUuid());
		$this->assertSame('Example Playlist', $this->playerEntity->getPlaylistName());
		$this->assertSame('loop', $this->playerEntity->getPlaylistMode());
		$this->assertEmpty($this->playerEntity->getCommands());
		$this->assertEmpty($this->playerEntity->getReports());
		$this->assertSame('1.0.0', $this->playerEntity->getFirmwareVersion());
		$this->assertSame('Player One', $this->playerEntity->getPlayerName());
		$this->assertSame(['zone1', 'zone2'], $this->playerEntity->getZones());
		$this->assertSame(['city' => 'New York'], $this->playerEntity->getLocationData());
		$this->assertSame('-74.0060', $this->playerEntity->getLocationLongitude());
		$this->assertSame('40.7128', $this->playerEntity->getLocationLatitude());
		$this->assertSame(['category1', 'category2'], $this->playerEntity->getCategories());
		$this->assertSame(['width' => 1920, 'height' => 1080], $this->playerEntity->getProperties());
		$this->assertSame(['enabled' => true], $this->playerEntity->getRemoteAdministration());
		$this->assertSame($screenTimes, $this->playerEntity->getScreenTimes());
		$this->configMock->expects($this->exactly(2))->method('getConfigValue')
			->willReturnMap([
				['report_server', 'player', 'https://reports.lan'],
				['index_server_url', 'player', 'https://index.lan']
			]);
		$this->assertSame('https://reports.lan', $this->playerEntity->getReportServer());
		$this->assertSame('https://index.lan/123e4567-e89b-12d3-a456-426614174000', $this->playerEntity->getIndexPath());
	}

	#[Group('units')]
	public function testConstructorWithDefaultData(): void
	{
		$this->userAgentHandlerMock->method('getModel')->willReturn(PlayerModel::COMPATIBLE);
		$this->userAgentHandlerMock->method('getUuid')->willReturn('123e4567-e89b-12d3-a456-426614174000');
		$this->userAgentHandlerMock->method('getFirmware')->willReturn('1.0.0');
		$this->userAgentHandlerMock->method('getName')->willReturn('Player One');

		$this->playerEntity = new PlayerEntity($this->configMock, $this->userAgentHandlerMock, []);

		$this->assertSame(1, $this->playerEntity->getPlayerId());
		$this->assertSame(0, $this->playerEntity->getPlaylistId());
		$this->assertSame(1, $this->playerEntity->getUID());
		$this->assertEquals(new DateTime('2025-01-01 00:00:00'), $this->playerEntity->getLastAccess());
		$this->assertEquals(new DateTime('2025-01-01 00:00:00'), $this->playerEntity->getLastUpdate());
		$this->assertEquals(new DateTime('2025-01-01 00:00:00'), $this->playerEntity->getLastUpdatePlaylist());
		$this->assertSame(0, $this->playerEntity->getDuration());
		$this->assertSame(0, $this->playerEntity->getStatus());
		$this->assertSame(900, $this->playerEntity->getRefresh());
		$this->assertSame(0, $this->playerEntity->getLicenceId());
		$this->assertSame('', $this->playerEntity->getPlaylistName());
		$this->assertSame('', $this->playerEntity->getPlaylistMode());
		$this->assertSame([], $this->playerEntity->getZones());
		$this->assertSame([], $this->playerEntity->getLocationData());
		$this->assertSame('', $this->playerEntity->getLocationLongitude());
		$this->assertSame('', $this->playerEntity->getLocationLatitude());
		$this->assertSame([], $this->playerEntity->getCategories());
		$this->assertSame(['width' => 1920, 'height' => 1080], $this->playerEntity->getProperties());
		$this->assertSame([], $this->playerEntity->getRemoteAdministration());
		$this->assertSame([], $this->playerEntity->getScreenTimes());
	}
}
