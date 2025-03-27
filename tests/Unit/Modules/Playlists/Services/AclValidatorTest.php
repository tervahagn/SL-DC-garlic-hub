<?php

namespace Tests\Unit\Modules\Playlists\Services;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Services\AclValidator;
use App\Modules\Users\Entities\UserEntity;
use App\Modules\Users\Services\UsersService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class AclValidatorTest extends TestCase
{
	private readonly AclValidator $aclValidator;
	private readonly UsersService $userServiceMock;
	private readonly Config $configMock;
	private readonly UserEntity $userEntityMock;

	/**
	 * @return void
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$this->userServiceMock = $this->createMock(UsersService::class);
		$this->configMock      = $this->createMock(Config::class);
		$this->userEntityMock = $this->createMock(UserEntity::class);

		$this->aclValidator    = new AclValidator('playlists', $this->userServiceMock, $this->configMock);
	}

	/**
	 * @throws CoreException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException|ModuleException
	 */
	#[Group('units')]
	public function testIsPlaylistEditableOwner()
	{
		$UID = 1;
		$playlist = ['UID' => 1, 'company_id' => 4, 'playlist_id' => 12];

		$this->userServiceMock->expects($this->never())->method('getUserById');
		$result = $this->aclValidator->isPlaylistEditable($UID, $playlist);

		$this->assertTrue($result);
	}

	/**
	 * @throws CoreException
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	#[Group('units')]
	public function testIsPlaylistEditableModuleAdmin()
	{
		$UID = 1;
		$playlist = ['UID' => 2, 'company_id' => 4, 'playlist_id' => 12];

		$this->userEntityMock->method('getAcl')->willReturn([['module' => 'playlists', 'acl' => 8]]);

		$this->mockConfigValues();

		$this->userServiceMock->expects($this->once())->method('getUserById')->willReturn($this->userEntityMock);
		$result = $this->aclValidator->isPlaylistEditable($UID, $playlist);

		$this->assertTrue($result);
	}

	#[Group('units')]
	public function testIsPlaylistEditableEdgeEdition()
	{
		$UID = 1;
		$playlist = ['UID' => 2, 'company_id' => 4, 'playlist_id' => 12];

		$this->configMock->method('getEdition')->willReturn(Config::PLATFORM_EDITION_EDGE);

		$result = $this->aclValidator->isPlaylistEditable($UID, $playlist);

		$this->assertFalse($result);
	}

	/**
	 * @throws \App\Framework\Exceptions\ModuleException
	 * @throws CoreException
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsPlaylistEditableMissingCompanyOrUID()
	{
		$UID = 1;
		$playlist = ['UID' => 2];

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Missing company id or UID in playlist data');

		$this->aclValidator->isPlaylistEditable($UID, $playlist);
	}


	#[Group('units')]
	public function testIsPlaylistEditableSubAdminAccessFailed()
	{
		$UID = 1;
		$playlist = ['UID' => 2, 'company_id' => 4, 'playlist_id' => 12];

		$this->userEntityMock->method('getAcl')->willReturn([['module' => 'playlists', 'acl' => 4]]);
		$this->userEntityMock->method('getVip')->willReturn([['playlists_subadmin' => 2]]); // different company
		$this->mockConfigValues();

		$this->configMock->method('getEdition')->willReturn(Config::PLATFORM_EDITION_CORE);

		$this->userServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclValidator->isPlaylistEditable($UID, $playlist);

		$this->assertTrue($result);
	}

	/**
	 * @throws CoreException
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsPlaylistEditableEditorAccess()
	{
		$UID = 1;
		$playlist = ['UID' => 2, 'company_id' => 4, 'playlist_id' => 12];

		$this->configMock->method('getEdition')->willReturn('core');
		$this->userServiceMock
			->method('isEditor')
			->with($UID)
			->willReturn(true);
		$this->userServiceMock
			->method('hasEditorAccessOnUnit')
			->with($UID, $playlist['playlist_id'])
			->willReturn(true);

		$result = $this->aclValidator->isPlaylistEditable($UID, $playlist);

		$this->assertTrue($result);
	}

	/**
	 * @throws CoreException
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsPlaylistEditableNoAccess()
	{
		$UID = 1;
		$playlist = ['UID' => 2, 'company_id' => 4, 'playlist_id' => 12];

		$this->configMock->method('getEdition')->willReturn('core');
		$this->userServiceMock
			->method('isSubAdmin')
			->with($UID)
			->willReturn(false);
		$this->userServiceMock
			->method('isEditor')
			->with($UID)
			->willReturn(false);

		$result = $this->aclValidator->isPlaylistEditable($UID, $playlist);

		$this->assertFalse($result);
	}


	/**
	 */
	private function mockConfigValues(): void
	{
		$this->configMock->method('getConfigValue')
			->willReturnCallback(function($key)
			{
				return match ($key)
				{
					'moduleadmin' => 8,
					'subadmin' => 4,
					'editor' => 2,
					'viewer' => 1,
					default => 0,
				};
			});
	}
}
