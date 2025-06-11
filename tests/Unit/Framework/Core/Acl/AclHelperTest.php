<?php

namespace Tests\Unit\Framework\Core\Acl;

use App\Framework\Core\Acl\AclHelper;
use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Modules\Users\Entities\UserEntity;
use App\Modules\Users\Services\UsersService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AclHelperTest extends TestCase
{
	private AclHelper $aclHelper;
	private Config&MockObject $configMock;
	private UsersService&MockObject $usersServiceMock;
	private UserEntity&MockObject $userEntityMock;

	protected function setUp(): void
	{
		$this->configMock       = $this->createMock(Config::class);
		$this->userEntityMock   = $this->createMock(UserEntity::class);
		$this->usersServiceMock = $this->createMock(UsersService::class);
		$this->aclHelper        = new AclHelper($this->usersServiceMock, $this->configMock);
	}

	#[Group('units')]
	public function testGetConfig(): void
	{
		$this->assertSame($this->configMock, $this->aclHelper->getConfig());
	}

	#[Group('units')]
	public function testIsModuleAdminReturnsTrueForValidAdmin(): void
	{
		$UID = 1;
		$moduleName = 'testModule';
		$this->mockConfigValues();

		$this->userEntityMock->method('getAcl')->willReturn([
			['module' => $moduleName, 'acl' => 8]
		]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->isModuleAdmin($UID, $moduleName);

		$this->assertTrue($result);
	}

	#[Group('units')]
	public function testIsModuleAdminReturnsFalseForInvalidAdmin(): void
	{
		$UID = 2;
		$moduleName = 'testModule';
		$this->mockConfigValues();

		$this->userEntityMock->method('getAcl')->willReturn([
			['module' => $moduleName, 'acl' => 4]
		]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->isModuleAdmin($UID, $moduleName);

		$this->assertFalse($result);
	}

	#[Group('units')]
	public function testIsSubAdminReturnsTrueForValidSubAdmin(): void
	{
		$UID = 1;
		$moduleName = 'testModule';
		$this->mockConfigValues();

		$this->userEntityMock->method('getAcl')->willReturn([
			['module' => $moduleName, 'acl' => 4]
		]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->isSubAdmin($UID, $moduleName);

		$this->assertTrue($result);
	}

	#[Group('units')]
	public function testIsSubAdminReturnsFalseForInvalidSubAdmin(): void
	{
		$UID = 2;
		$moduleName = 'testModule';
		$this->mockConfigValues();

		$this->userEntityMock->method('getAcl')->willReturn([
			['module' => $moduleName, 'acl' => 2]
		]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->isSubAdmin($UID, $moduleName);

		$this->assertFalse($result);
	}

	#[Group('units')]
	public function testIsEditorReturnsTrueForValidEditor(): void
	{
		$UID = 1;
		$moduleName = 'testModule';
		$this->mockConfigValues();

		$this->userEntityMock->method('getAcl')->willReturn([
			['module' => $moduleName, 'acl' => 2]
		]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->isEditor($UID, $moduleName);

		$this->assertTrue($result);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws Exception
	 */
	#[Group('units')]
	public function testIsEditorReturnsFalseForInvalidEditor(): void
	{
		$UID = 1;
		$moduleName = 'testModule';
		$this->mockConfigValues();

		$this->userEntityMock->method('getAcl')->willReturn([
			['module' => $moduleName, 'acl' => 1]
		]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->isEditor($UID, $moduleName);

		$this->assertFalse($result);
	}

	#[Group('units')]
	public function testIsViewerReturnsTrueForValidViewer(): void
	{
		$UID = 1;
		$moduleName = 'testModule';
		$this->mockConfigValues();

		$this->userEntityMock->method('getAcl')->willReturn([
			['module' => $moduleName, 'acl' => 1]
		]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->isViewer($UID, $moduleName);

		$this->assertTrue($result);
	}

	#[Group('units')]
	public function testIsViewerReturnsFalseForInvalidViewer(): void
	{
		$UID = 1;
		$moduleName = 'testModule';
		$this->mockConfigValues();

		$this->userEntityMock->method('getAcl')->willReturn([
			['module' => $moduleName, 'acl' => 0]
		]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->isViewer($UID, $moduleName);

		$this->assertFalse($result);
	}

	#[Group('units')]
	public function testHasSubAdminAccessOnCompanyReturnsTrueForValidAccess(): void
	{
		$UID = 1;
		$companyId = 100;
		$moduleName = 'testModule_subadmin';

		$this->userEntityMock->method('getVip')->willReturn([
			[$moduleName => $companyId]
		]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->hasSubAdminAccessOnCompany($UID, $companyId, 'testModule');

		$this->assertTrue($result);
	}

	#[Group('units')]
	public function testHasSubAdminAccessOnCompanyReturnsFalseForInvalidAccess(): void
	{
		$UID = 1;
		$companyId = 100;
		$moduleName = 'testModule_subadmin';

		$this->userEntityMock->method('getVip')->willReturn([
			[$moduleName => 101]
		]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->hasSubAdminAccessOnCompany($UID, $companyId, 'testModule');

		$this->assertFalse($result);
	}

	#[Group('units')]
	public function testHasSubAdminAccessOnCompanyReturnsFalseWhenVIPIsEmpty(): void
	{
		$UID = 1;
		$companyId = 100;
		$moduleName = 'testModule_subadmin';

		$this->userEntityMock->method('getVip')->willReturn([]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->hasSubAdminAccessOnCompany($UID, $companyId, 'testModule');
		$this->assertFalse($result);
	}

	#[Group('units')]
	public function testHasEditorAccessOnUnitReturnsTrueForValidAccess(): void
	{
		$UID = 1;
		$unitId = 200;
		$moduleName = 'testModule_editor';

		$this->userEntityMock->method('getVip')->willReturn([
			[$moduleName => $unitId]
		]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->hasEditorAccessOnUnit($UID, $unitId, 'testModule');

		$this->assertTrue($result);
	}

	#[Group('units')]
	public function testHasViewerAccessOnUnitReturnsTrueForValidAccess(): void
	{
		$UID = 1;
		$unitId = 300;
		$moduleName = 'testModule_viewer';

		$this->userEntityMock->method('getVip')->willReturn([
			[$moduleName => $unitId]
		]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->hasViewerAccessOnUnit($UID, $unitId, 'testModule');

		$this->assertTrue($result);
	}

	#[Group('units')]
	public function testHasViewerAccessOnUnitReturnsFalseForInvalidAccess(): void
	{
		$UID = 1;
		$unitId = 300;
		$moduleName = 'testModule_viewer';

		$this->userEntityMock->method('getVip')->willReturn([
			[$moduleName => 301]
		]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->hasViewerAccessOnUnit($UID, $unitId, 'testModule');

		$this->assertFalse($result);
	}

	#[Group('units')]
	public function testHasViewerAccessOnUnitReturnsFalseWhenVIPIsEmpty(): void
	{
		$UID = 1;
		$unitId = 300;
		$moduleName = 'testModule_viewer';

		$this->userEntityMock->method('getVip')->willReturn([]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->hasViewerAccessOnUnit($UID, $unitId, 'testModule');

		$this->assertFalse($result);
	}

	#[Group('units')]
	public function testHasEditorAccessOnUnitReturnsFalseForInvalidAccess(): void
	{
		$UID = 1;
		$unitId = 200;
		$moduleName = 'testModule_editor';

		$this->userEntityMock->method('getVip')->willReturn([
			[$moduleName => 201]
		]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->hasEditorAccessOnUnit($UID, $unitId, 'testModule');

		$this->assertFalse($result);
	}

	#[Group('units')]
	public function testHasEditorAccessOnUnitReturnsFalseWhenVIPIsEmpty(): void
	{
		$UID = 1;
		$unitId = 200;
		$moduleName = 'testModule_editor';

		$this->userEntityMock->method('getVip')->willReturn([]);

		$this->usersServiceMock->method('getUserById')->willReturn($this->userEntityMock);

		$result = $this->aclHelper->hasEditorAccessOnUnit($UID, $unitId, 'testModule');
		$this->assertFalse($result);
	}

	private function mockConfigValues(): void
	{
		$this->configMock->method('getConfigValue')
			->willReturnCallback(function ($key)
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
