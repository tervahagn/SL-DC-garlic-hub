<?php

namespace Tests\Unit\Modules\Player\Services;

use App\Framework\Core\Acl\AclHelper;
use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Player\Services\AclValidator;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AclValidatorTest extends TestCase
{
	private AclValidator $aclValidator;
	private AclHelper&MockObject $aclHelperMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->aclHelperMock = $this->createMock(AclHelper::class);

		$this->aclValidator  = new AclValidator($this->aclHelperMock);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsPlaylistEditableModuleAdmin()
	{
		$UID = 1;
		$player = ['UID' => 2, 'company_id' => 4, 'player_id' => 12];

		$this->aclHelperMock->expects($this->once())->method('isModuleAdmin')
			->with($UID, 'player')
			->willReturn(true);
		$result = $this->aclValidator->isPlayerEditable($UID, $player);

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
		$player = ['UID' => 2, 'company_id' => 4, 'player_id' => 12];

		$configMock = $this->createMock(Config::class);
		$this->aclHelperMock->expects($this->once())->method('getConfig')->willReturn($configMock);
		$configMock->method('getEdition')->willReturn(Config::PLATFORM_EDITION_EDGE);

		$result = $this->aclValidator->isPlayerEditable($UID, $player);

		$this->assertFalse($result);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsPlaylistEditableSubAdminAccessFailed()
	{
		$UID = 1;
		$player = ['UID' => 2, 'company_id' => 4, 'player_id' => 12];

		$this->aclHelperMock->method('isSubAdmin')
			->with($UID, 'player')
			->willReturn(true);

		$this->aclHelperMock->method('hasSubAdminAccessOnCompany')
			->with($UID, $player['company_id'], 'player')
			->willReturn(false);

		$result = $this->aclValidator->isPlayerEditable($UID, $player);

		$this->assertFalse($result);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsPlaylistEditableSubAdminAccessSucceed()
	{
		$UID = 1;
		$player = ['UID' => 2, 'company_id' => 4, 'player_id' => 12];

		$this->aclHelperMock->method('isSubAdmin')
			->with($UID, 'player')
			->willReturn(true);

		$this->aclHelperMock->method('hasSubAdminAccessOnCompany')
			->with($UID, $player['company_id'], 'player')
			->willReturn(true);

		$result = $this->aclValidator->isPlayerEditable($UID, $player);

		$this->assertTrue($result);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsPlaylistEditableEditorAccess()
	{
		$UID = 1;
		$player = ['UID' => 2, 'company_id' => 4, 'player_id' => 12];

		$this->aclHelperMock->method('isEditor')
			->with($UID, 'player')
			->willReturn(true);

		$this->aclHelperMock->method('hasEditorAccessOnUnit')
			->with($UID, $player['player_id'], 'player')
			->willReturn(true);

		$result = $this->aclValidator->isPlayerEditable($UID, $player);

		$this->assertTrue($result);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsPlaylistEditableNoAccess()
	{
		$UID = 1;
		$player = ['UID' => 2, 'company_id' => 4, 'player_id' => 12];

		$this->aclHelperMock->method('isEditor')
			->with($UID, 'player')
			->willReturn(true);

		$this->aclHelperMock->method('hasEditorAccessOnUnit')
			->with($UID, $player['player_id'], 'player')
			->willReturn(false);

		$result = $this->aclValidator->isPlayerEditable($UID, $player);

		$this->assertFalse($result);
	}

}
