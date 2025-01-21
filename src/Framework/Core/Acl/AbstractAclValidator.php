<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

namespace App\Framework\Core\Acl;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\User\UserService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;

/**
 * Class AbstractAclValidator
 *
 * Class is user agnostic and provides atomar functions to determine
 * user access rights from a user entity
 *
 * @see /docs/user-administration.md
 */
abstract class AbstractAclValidator
{
	const string SECTION_GLOBAL_ACLS = 'GlobalACLs';
	protected readonly string $moduleName;
	protected readonly UserService $userService;
	protected readonly Config $config;

	public function __construct(string $moduleName, UserService $userService, Config $config)
	{
		$this->moduleName    = $moduleName;
		$this->userService   = $userService;
		$this->config        = $config;
	}

	/**
	 * @param int $UID
	 * @return bool
	 * @throws CoreException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function isModuleAdmin(int $UID): bool
	{
		return $this->hasGlobalAcl($UID, AclSections::MODULEADMIN->value);
	}

	/**
	 * @param int $UID
	 * @return bool
	 * @throws CoreException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function isSubAdmin(int $UID): bool
	{
		return $this->hasGlobalAcl($UID, AclSections::SUBADMIN->value);
	}

	/**
	 * @param int $UID
	 * @return bool
	 * @throws CoreException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function isEditor(int $UID): bool
	{
		return $this->hasGlobalAcl($UID, AclSections::EDITOR->value);
	}

	/**
	 * @param int $UID
	 * @return bool
	 * @throws CoreException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function isViewer(int $UID): bool
	{
		return $this->hasGlobalAcl($UID, AclSections::VIEWER->value);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function hasSubAdminAccessOnCompany(int $UID, int $company_id): bool
	{
		if (empty($company_id) || !$this->isSubAdmin($UID))
			return false;

		$userEntity = $this->userService->getUserById($UID);
		$vipName    = $this->moduleName.'_'.AclSections::SUBADMIN->value;

		return in_array($company_id, $userEntity->getVip()[$vipName]);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function hasEditorAccessOnUnit($UID, int|string $unitId): bool
	{
		if (empty($unitId) || !$this->isEditor($UID))
			return false;

		$userEntity = $this->userService->getUserById($UID);
		$vipName    = $this->moduleName.'_'.AclSections::SUBADMIN->value;

		return in_array($unitId, $userEntity->getVip()[$vipName]);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function hasViewerAccessOnUnit(int $UID, int|string $unitId): bool
	{
		if (empty($unitId))
			return false;

		$userEntity = $this->userService->getUserById($UID);
		$vipName    = $this->moduleName.'_'.AclSections::SUBADMIN->value;

		return in_array($unitId, $userEntity->getVip()[$vipName]);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	protected function hasGlobalAcl(int $UID, string $aclName): bool
	{
		$userEntity = $this->userService->getUserById($UID);
		$acls       = $userEntity->getAcl();

		$aclValue = $this->config->getConfigValue($aclName, $this->moduleName, self::SECTION_GLOBAL_ACLS);

		foreach ($acls as $acl)
		{
			if ($acl['module'] === $this->moduleName && ($acl['acl'] & $aclValue) > 0)
				return true;
		}

		return false;
	}

}