<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


namespace Tests\Unit\Modules\Mediapool\Services;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\Services\AclValidator;
use App\Modules\Users\Entities\UserEntity;
use App\Modules\Users\Services\UserService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class AclValidatorTest extends TestCase
{
	private readonly AclValidator $aclValidator;
	private readonly UserService $userServiceMock;
	private readonly Config $configMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->userServiceMock = $this->createMock(UserService::class);
		$this->configMock      = $this->createMock(Config::class);
		$this->aclValidator    = new AclValidator('mediapool', $this->userServiceMock, $this->configMock);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testCheckDirectoryPermissionsFailsNoUID()
	{
		$UID       = 1;
		$directory = [];
		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Missing UID in media directory data struct.');

		$this->aclValidator->checkDirectoryPermissions($UID, $directory);

	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCheckDirectoryPermissionsOwnerIsSame()
	{
		$UID = 1;
		$directory = ['UID' => $UID, 'company_id' => 1, 'node_id' => 1, 'visibility' => 0, 'parent_id' => 0];


		$userEntityMock = $this->createMock(UserEntity::class);
		$userEntityMock->method('getAcl')->willReturn([['module' => 'Mediapool', 'acl' => 0]]);
		$this->userServiceMock->method('getUserById')->willReturn($userEntityMock);
		$permissions = $this->aclValidator->checkDirectoryPermissions($UID, $directory);

		$this->assertEquals(['create' => true, 'read' => true, 'edit' => true, 'share' => 'global'], $permissions);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCheckDirectoryPermissionsIsModuleAdmin()
	{
		$UID = 1;
		$directory = ['UID' => 2, 'company_id' => 1, 'node_id' => 1, 'visibility' => 0, 'parent_id' => 0];

		$userEntityMock = $this->createMock(UserEntity::class);
		$userEntityMock->method('getAcl')->willReturn([['module' => 'mediapool', 'acl' => 8]]);

		$this->mockConfigValues();

		$this->userServiceMock->method('getUserById')->willReturn($userEntityMock);
		$permissions = $this->aclValidator->checkDirectoryPermissions($UID, $directory);

		$this->assertEquals(['create' => true, 'read' => true, 'edit' => true, 'share' => 'global'], $permissions);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCheckDirectoryPermissionsIsSubAdminNoAccess()
	{
		$UID = 1;
		$directory = ['UID' => 2, 'company_id' => 1, 'node_id' => 1, 'visibility' => 0, 'parent_id' => 0];

		$userEntityMock = $this->createMock(UserEntity::class);
		$userEntityMock->method('getAcl')->willReturn([['module' => 'mediapool', 'acl' => 4]]);
		$userEntityMock->method('getVip')->willReturn([]);

		$this->mockConfigValues();

		$this->userServiceMock->method('getUserById')->willReturn($userEntityMock);
		$permissions = $this->aclValidator->checkDirectoryPermissions($UID, $directory);

		$this->assertEquals(['create' => false, 'read' => false, 'edit' => false, 'share' => ''], $permissions);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCheckDirectoryPermissionsIsSubAdminWrongAccess()
	{
		$UID = 1;
		$directory = ['UID' => 2, 'company_id' => 1, 'node_id' => 1, 'visibility' => 0, 'parent_id' => 0];


		$userEntityMock = $this->createMock(UserEntity::class);
		$userEntityMock->method('getAcl')->willReturn([['module' => 'mediapool', 'acl' => 4]]);
		$userEntityMock->method('getVip')->willReturn([['mediapool_subadmin' => 2]]); // different company

		$this->mockConfigValues();

		$this->userServiceMock->method('getUserById')->willReturn($userEntityMock);
		$permissions = $this->aclValidator->checkDirectoryPermissions($UID, $directory);

		$this->assertEquals(['create' => false, 'read' => false, 'edit' => false, 'share' => ''], $permissions);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCheckDirectoryPermissionsIsSubAdminSucceedRootDir()
	{
		$UID = 1;
		$directory = ['UID' => 2, 'company_id' => 4, 'node_id' => 1, 'visibility' => 0, 'parent_id' => 0];

		$userEntityMock = $this->createMock(UserEntity::class);
		$userEntityMock->method('getAcl')->willReturn([['module' => 'mediapool', 'acl' => 4]]);
		$userEntityMock->method('getVip')->willReturn([['mediapool_subadmin' => 4]]); // same company

		$this->mockConfigValues();

		$this->userServiceMock->method('getUserById')->willReturn($userEntityMock);
		$permissions = $this->aclValidator->checkDirectoryPermissions($UID, $directory);

		$this->assertEquals(['create' => true, 'read' => true, 'edit' => false, 'share' => 'company'], $permissions);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCheckDirectoryPermissionsIsEditorWrongAccess()
	{
		$UID = 1;
		$directory = ['UID' => 2, 'company_id' => 4, 'node_id' => 1, 'visibility' => 0, 'parent_id' => 12];

		$userEntityMock = $this->createMock(UserEntity::class);
		$userEntityMock->method('getAcl')->willReturn([['module' => 'mediapool', 'acl' => 2]]);
		$userEntityMock->method('getVip')->willReturn([['mediapool_editor' => 3]]); // wrong Node-id

		$this->mockConfigValues();

		$this->userServiceMock->method('getUserById')->willReturn($userEntityMock);
		$permissions = $this->aclValidator->checkDirectoryPermissions($UID, $directory);

		$this->assertEquals(['create' => false, 'read' => false, 'edit' => false, 'share' => ''], $permissions);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCheckDirectoryPermissionsIsSubAdminSucceedNormalDir()
	{
		$UID = 1;
		$directory = ['UID' => 2, 'company_id' => 4, 'node_id' => 1, 'visibility' => 0, 'parent_id' => 12];

		$userEntityMock = $this->createMock(UserEntity::class);
		$userEntityMock->method('getAcl')->willReturn([['module' => 'mediapool', 'acl' => 4]]);
		$userEntityMock->method('getVip')->willReturn([['mediapool_subadmin' => 4]]); // same company

		$this->mockConfigValues();

		$this->userServiceMock->method('getUserById')->willReturn($userEntityMock);
		$permissions = $this->aclValidator->checkDirectoryPermissions($UID, $directory);

		$this->assertEquals(['create' => true, 'read' => true, 'edit' => true, 'share' => 'company'], $permissions);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCheckDirectoryPermissionsIsEditorSucceed()
	{
		$UID = 1;
		$directory = ['UID' => 2, 'company_id' => 4, 'node_id' => 1, 'visibility' => 0, 'parent_id' => 12];

		$userEntityMock = $this->createMock(UserEntity::class);
		$userEntityMock->method('getAcl')->willReturn([['module' => 'mediapool', 'acl' => 2]]);
		$userEntityMock->method('getVip')->willReturn([['mediapool_editor' => 1]]); // same node

		$this->mockConfigValues();

		$this->userServiceMock->method('getUserById')->willReturn($userEntityMock);
		$permissions = $this->aclValidator->checkDirectoryPermissions($UID, $directory);

		$this->assertEquals(['create' => true, 'read' => true, 'edit' => false, 'share' => ''], $permissions);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCheckDirectoryPermissionsIsViewerWrongNode()
	{
		$UID = 1;
		$directory = ['UID' => 2, 'company_id' => 4, 'node_id' => 1, 'visibility' => 0, 'parent_id' => 12];

		$userEntityMock = $this->createMock(UserEntity::class);
		$userEntityMock->method('getAcl')->willReturn([['module' => 'mediapool', 'acl' => 1]]);
		$userEntityMock->method('getVip')->willReturn([['mediapool_viewer' => 13]]); // wrong node

		$this->mockConfigValues();

		$this->userServiceMock->method('getUserById')->willReturn($userEntityMock);
		$permissions = $this->aclValidator->checkDirectoryPermissions($UID, $directory);

		$this->assertEquals(['create' => false, 'read' => false, 'edit' => false, 'share' => ''], $permissions);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCheckDirectoryPermissionsIsViewerSucceed()
	{
		$UID = 1;
		$directory = ['UID' => 2, 'company_id' => 4, 'node_id' => 1, 'visibility' => 0, 'parent_id' => 12];

		$userEntityMock = $this->createMock(UserEntity::class);
		$userEntityMock->method('getAcl')->willReturn([['module' => 'mediapool', 'acl' => 1]]);
		$userEntityMock->method('getVip')->willReturn([['mediapool_viewer' => 1]]); // same node

		$this->mockConfigValues();

		$this->userServiceMock->method('getUserById')->willReturn($userEntityMock);
		$permissions = $this->aclValidator->checkDirectoryPermissions($UID, $directory);

		$this->assertEquals(['create' => false, 'read' => true, 'edit' => false, 'share' => ''], $permissions);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCheckDirectoryPermissionsIsPublic()
	{
		$UID = 1;
		$directory = ['UID' => 2, 'company_id' => 4, 'node_id' => 1, 'visibility' => AclValidator::VISIBILITY_PUBLIC, 'parent_id' => 0];

		$userEntityMock = $this->createMock(UserEntity::class);
		$userEntityMock->method('getAcl')->willReturn([]);
		$userEntityMock->expects($this->never())->method('getVip');

		$this->mockConfigValues();

		$this->userServiceMock->method('getUserById')->willReturn($userEntityMock);
		$permissions = $this->aclValidator->checkDirectoryPermissions($UID, $directory);

		$this->assertEquals(['create' => false, 'read' => true, 'edit' => false, 'share' => ''], $permissions);
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
