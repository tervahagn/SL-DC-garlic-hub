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

/**
 * Class AbstractAclValidator
 *
 * Class is user agnostic and provides atomar functions to determine user access rights from a userentt
 *
 *
 */
abstract class AbstractAclValidator
{
	const string SECTION_GLOBAL_ACLS = 'GlobalACLs';
	const string SECTION_ACL_VIP_NAMES = 'VipNames';
	protected readonly string $moduleName;
	protected readonly UserService $userService;
	protected readonly Config $config;

	private array $cache = [];

	public function __construct(string $moduleName, UserService $userService, Config $config)
	{
		$this->moduleName    = $moduleName;
		$this->userService   = $userService;
		$this->config        = $config;
	}

	/**
	 * @throws CoreException
	 */
	public function getAclNameModuleAdmin(): string
	{
		return $this->config->getConfigValue('moduleadmin', $this->moduleName, self::SECTION_GLOBAL_ACLS);
	}

	/**
	 * @throws CoreException
	 */
	public function getAclNameSubAdmin(): string
	{
		return $this->config->getConfigValue('subadmin', $this->moduleName, self::SECTION_GLOBAL_ACLS);
	}

	/**
	 * @throws CoreException
	 */
	public function getAclNameEditor(): string
	{
		return $this->config->getConfigValue('editor', $this->moduleName, self::SECTION_GLOBAL_ACLS);
	}

	/**
	 * @throws CoreException
	 */
	public function getAclNameViewer(): string
	{
		return $this->config->getConfigValue('viewer', $this->moduleName, self::SECTION_GLOBAL_ACLS);
	}
	public function getSubAdminVipName(): string
	{
		return $this->config->getConfigValue('subadmin', $this->moduleName, self::SECTION_ACL_VIP_NAMES);

	}
	public function getEditorVipName(): string
	{
		return $this->config->getConfigValue('editor', $this->moduleName, self::SECTION_ACL_VIP_NAMES);

	}
	public function getViewerVipName(): string
	{
		return $this->config->getConfigValue('viewer', $this->moduleName, self::SECTION_ACL_VIP_NAMES);
	}

	/**
	 * @throws CoreException
	 */
	public function isModuleAdmin(int $UID): bool
	{
		return $this->hasGlobalAcl($UID, $this->getAclNameModuleAdmin());
	}

	/**
	 * @throws CoreException
	 */
	public function isSubAdmin(int $UID): bool
	{
		return $this->hasGlobalAcl($UID, $this->getAclNameSubAdmin());
	}

	/**
	 * @throws CoreException
	 */
	public function isEditor(int $UID): bool
	{
		return $this->hasGlobalAcl($UID, $this->getAclNameEditor());
	}

	/**
	 * @throws CoreException
	 */
	public function isViewer(int $UID): bool
	{
		return $this->hasGlobalAcl($UID, $this->getAclNameViewer());
	}

	/**
	 * @throws CoreException
	 */
	public function hasSubAdminAccess(int $UID, int $company_id): bool
	{
		if (empty($company_id) || !$this->isSubAdmin())
			return false;

		$userEntity = $this->userService->getUserById($UID);

		return in_array($company_id, $userEntity->getVip()[$this->getSubAdminVipName()]);

	}

	/**
	 * @throws CoreException
	 */
	public function hasEditorAccess($UID, int|string $unit_id): bool
	{
		if (empty($unit_id) || !$this->isEditor())
			return false;

		$userEntity = $this->userService->getUserById($UID);

		return in_array($unit_id, $userEntity->getVip()[$this->getEditorVipName()]);
	}

	public function hasViewerAccess(int $UID, int|string $unit_id): bool
	{
		if (empty($unit_id))
			return false;

		$userEntity = $this->userService->getUserById($UID);

		return in_array($unit_id, $userEntity->getVip()[$this->getViewerVipName()]);
	}

	public function determineCompaniesForSubAdmin(int $UID): array
	{
		$userEntity = $this->userService->getUserById($UID);

		return $userEntity->getVip()[$this->getViewerVipName()];
	}

	/**
	 * @throws CoreException
	 */
	protected function hasGlobalAcl(int $UID, string $aclName): bool
	{
		$value = $this->config->getConfigValue($aclName, $this->moduleName, self::SECTION_GLOBAL_ACLS);
		return $this->validateAcl($UID, $value);
	}

	protected function validateAcl(int $UID, string $aclName): bool
	{
		$userEntity = $this->userService->getUserById($UID);
		$acls       = $userEntity->getAcl();
		return array_key_exists($this->moduleName, $acls) && $acls[$this->moduleName] === $aclName;
	}
}