<?php

namespace Tests\Unit\Modules\Playlists\Services;

use App\Framework\Core\Acl\AclHelper;
use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Services\AclValidator;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class AclValidatorTest extends TestCase
{
	private readonly AclValidator $aclValidator;
	private readonly AclHelper $aclHelperMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->aclHelperMock = $this->createMock(AclHelper::class);

		$this->aclValidator    = new AclValidator($this->aclHelperMock);
	}

	/**
	 * @throws CoreException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws PhpfastcacheSimpleCacheException|ModuleException
	 */
	#[Group('units')]
	public function testIsPlaylistEditableOwner()
	{
		$UID = 1;
		$playlist = ['UID' => 1, 'company_id' => 4, 'playlist_id' => 12];

		$this->aclHelperMock->expects($this->never())->method('isModuleAdmin');
		$result = $this->aclValidator->isPlaylistEditable($UID, $playlist);

		$this->assertTrue($result);
	}

	/**
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws PhpfastcacheSimpleCacheException
	 */
	#[Group('units')]
	public function testIsPlaylistEditableModuleAdmin()
	{
		$UID = 1;
		$playlist = ['UID' => 2, 'company_id' => 4, 'playlist_id' => 12];

		$this->aclHelperMock->expects($this->once())->method('isModuleAdmin')
			->with($UID, 'playlists')
			->willReturn(true);
		$result = $this->aclValidator->isPlaylistEditable($UID, $playlist);

		$this->assertTrue($result);
	}

	/**
	 * @throws ModuleException
	 * @throws Exception
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsPlaylistEditableEdgeEdition()
	{
		$UID = 1;
		$playlist = ['UID' => 2, 'company_id' => 4, 'playlist_id' => 12];

		$configMock = $this->createMock(Config::class);
		$this->aclHelperMock->expects($this->once())->method('getConfig')->willReturn($configMock);
		$configMock->method('getEdition')->willReturn(Config::PLATFORM_EDITION_EDGE);

		$result = $this->aclValidator->isPlaylistEditable($UID, $playlist);

		$this->assertFalse($result);
	}

	/**
	 * @throws \App\Framework\Exceptions\ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsPlaylistEditableMissingCompanyOrUID()
	{
		$UID = 1;
		$playlist = ['UID' => 2];

		$configMock = $this->createMock(Config::class);
		$this->aclHelperMock->expects($this->once())->method('getConfig')->willReturn($configMock);
		$configMock->expects($this->once())->method('getEdition')->willReturn(Config::PLATFORM_EDITION_ENTERPRISE);

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Missing company id or UID in unit data.');

		$this->aclValidator->isPlaylistEditable($UID, $playlist);
	}


	#[Group('units')]
	public function testIsPlaylistEditableSubAdminAccessFailed()
	{
		$UID = 1;
		$playlist = ['UID' => 2, 'company_id' => 4, 'playlist_id' => 12];

		$this->aclHelperMock->method('isSubAdmin')
			->with($UID, 'playlists')
			->willReturn(true);

		$this->aclHelperMock->method('hasSubAdminAccessOnCompany')
			->with($UID, $playlist['company_id'], 'playlists')
			->willReturn(false);

		$result = $this->aclValidator->isPlaylistEditable($UID, $playlist);

		$this->assertFalse($result);
	}

	#[Group('units')]
	public function testIsPlaylistEditableSubAdminAccessSucceed()
	{
		$UID = 1;
		$playlist = ['UID' => 2, 'company_id' => 4, 'playlist_id' => 12];

		$this->aclHelperMock->method('isSubAdmin')
			->with($UID, 'playlists')
			->willReturn(true);

		$this->aclHelperMock->method('hasSubAdminAccessOnCompany')
			->with($UID, $playlist['company_id'], 'playlists')
			->willReturn(true);

		$result = $this->aclValidator->isPlaylistEditable($UID, $playlist);

		$this->assertTrue($result);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsPlaylistEditableEditorAccess()
	{
		$UID = 1;
		$playlist = ['UID' => 2, 'company_id' => 4, 'playlist_id' => 12];

		$this->aclHelperMock->method('isEditor')
			->with($UID, 'playlists')
			->willReturn(true);

		$this->aclHelperMock->method('hasEditorAccessOnUnit')
			->with($UID, $playlist['playlist_id'], 'playlists')
			->willReturn(true);

		$result = $this->aclValidator->isPlaylistEditable($UID, $playlist);

		$this->assertTrue($result);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsPlaylistEditableNoAccess()
	{
		$UID = 1;
		$playlist = ['UID' => 2, 'company_id' => 4, 'playlist_id' => 12];

		$this->aclHelperMock->method('isEditor')
			->with($UID, 'playlists')
			->willReturn(true);

		$this->aclHelperMock->method('hasEditorAccessOnUnit')
			->with($UID, $playlist['playlist_id'], 'playlists')
			->willReturn(false);

		$result = $this->aclValidator->isPlaylistEditable($UID, $playlist);

		$this->assertFalse($result);
	}


}
