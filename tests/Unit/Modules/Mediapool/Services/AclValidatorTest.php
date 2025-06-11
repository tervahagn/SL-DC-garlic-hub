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

use App\Framework\Core\Acl\AclHelper;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\Services\AclValidator;
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

		$this->aclValidator    = new AclValidator($this->aclHelperMock);
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
	 */
	#[Group('units')]
	public function testCheckDirectoryPermissionsOwnerIsSame()
	{
		$UID = 1;
		$directory = ['UID' => $UID, 'company_id' => 1, 'node_id' => 1, 'visibility' => 0, 'parent_id' => 0];

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

		$this->aclHelperMock->method('isModuleAdmin')
			->with($UID, 'mediapool')
			->willReturn(true);

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
	public function testCheckDirectoryPermissionsIsSubAdminWrongAccess()
	{
		$UID = 1;
		$directory = ['UID' => 2, 'company_id' => 1, 'node_id' => 1, 'visibility' => 0, 'parent_id' => 0];

		$this->aclHelperMock->method('isSubAdmin')
			->with($UID, 'mediapool')
			->willReturn(true);

		$this->aclHelperMock->method('hasSubAdminAccessOnCompany')
			->with($UID, $directory['company_id'], 'mediapool')
			->willReturn(false);

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

		$this->aclHelperMock->method('isSubAdmin')
			->with($UID, 'mediapool')
			->willReturn(true);

		$this->aclHelperMock->method('hasSubAdminAccessOnCompany')
			->with($UID, $directory['company_id'], 'mediapool')
			->willReturn(true);

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

		$this->aclHelperMock->method('isEditor')
			->with($UID, 'mediapool')
			->willReturn(true);

		$this->aclHelperMock->method('hasEditorAccessOnUnit')
			->with($UID, $directory['node_id'], 'mediapool')
			->willReturn(false);

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

		$this->aclHelperMock->method('isSubAdmin')
			->with($UID, 'mediapool')
			->willReturn(true);

		$this->aclHelperMock->method('hasSubAdminAccessOnCompany')
			->with($UID, $directory['company_id'], 'mediapool')
			->willReturn(true);

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

		$this->aclHelperMock->method('isEditor')
			->with($UID, 'mediapool')
			->willReturn(true);

		$this->aclHelperMock->method('hasEditorAccessOnUnit')
			->with($UID, $directory['node_id'], 'mediapool')
			->willReturn(true);

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

		$this->aclHelperMock->method('isViewer')
			->with($UID, 'mediapool')
			->willReturn(true);

		$this->aclHelperMock->method('hasViewerAccessOnUnit')
			->with($UID, $directory['node_id'], 'mediapool')
			->willReturn(false);

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

		$this->aclHelperMock->method('isViewer')
			->with($UID, 'mediapool')
			->willReturn(true);

		$this->aclHelperMock->method('hasViewerAccessOnUnit')
			->with($UID, $directory['node_id'], 'mediapool')
			->willReturn(true);

		$permissions = $this->aclValidator->checkDirectoryPermissions($UID, $directory);

		$this->assertEquals(['create' => false, 'read' => true, 'edit' => false, 'share' => ''], $permissions);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testCheckDirectoryPermissionsIsPublic()
	{
		$UID = 1;
		$directory = ['UID' => 2, 'company_id' => 4, 'node_id' => 1, 'visibility' => AclValidator::VISIBILITY_PUBLIC, 'parent_id' => 0];

		$permissions = $this->aclValidator->checkDirectoryPermissions($UID, $directory);

		$this->assertEquals(['create' => false, 'read' => true, 'edit' => false, 'share' => ''], $permissions);
	}


}
