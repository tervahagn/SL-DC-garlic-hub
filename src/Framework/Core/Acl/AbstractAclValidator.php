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
use App\Framework\User\Edge\UserMainRepository;
use App\Framework\User\Enterprise\UserVipRepository;
use App\Framework\User\UserEntity;

/**
 * Class AbstractAclValidator
 *
 * Includes a cache mechanismen to prevent repeated
 * access to the database
 *
 */
abstract class AbstractAclValidator
{
	const string GLOBAL_ACLS = 'GlobalACLs';

	const int USER_STATUS_DEFAULT_USER 	= 1;
	const int USER_STATUS_ADMIN_USER	= 2;
	protected readonly string $moduleName;
	protected readonly UserEntity $userEntity;
	protected readonly UserMainRepository $userMainRepository;
	protected readonly UserVipRepository $userVipRepository;
	protected readonly Config $config;

	private array $cache = [];

	public function __construct(string $moduleName, UserEntity $user, UserMainRepository $userMainRepository, UserVipRepository $userVip, Config $config)
	{
		$this->moduleName         = $moduleName;
		$this->userEntity         = $user;
		$this->userVipRepository  = $userVip;
		$this->userMainRepository = $userMainRepository;
		$this->config             = $config;
	}

	abstract public function getAclNameModuleAdmin();
	abstract public function getAclNameSubAdmin();
	abstract public function getSubAdminUserModuleName();
	abstract public function getAclNameEditor();
	abstract public function getAclNameViewer();
	abstract public function getEditorModuleName();
	abstract public function getViewerModuleName();

	/**
	 * @throws CoreException
	 */
	public function isModuleAdmin(): bool
	{
		return $this->hasGlobalAcl($this->getAclNameModuleAdmin());
	}

	/**
	 * @throws CoreException
	 */
	public function isSubAdmin(): bool
	{
		return $this->hasGlobalAcl($this->getAclNameSubAdmin());
	}

	/**
	 * @throws CoreException
	 */
	public function isEditor(): bool
	{
		return $this->hasGlobalAcl($this->getAclNameEditor());
	}

	/**
	 * @throws CoreException
	 */
	public function isViewer(): bool
	{
		return $this->hasGlobalAcl($this->getAclNameViewer());
	}


	/**
	 * @throws CoreException
	 */
	public function isUnitEditable(int|string $unit_id): bool
	{
		if (empty($unit_id) || !$this->isEditor())
			return false;

		return $this->getCachedResult("isEditorEditable_$unit_id", function () use ($unit_id) {
			$local_acl = $this->userVipRepository->findOneAclByUIDModuleAndDataNum(
				$this->userEntity->getMain()['UID'],
				$this->getEditorModuleName(),
				$unit_id
			);

			return ($local_acl > 0);
		});
	}

	public function isUnitViewable($unit_id): bool
	{
		if (empty($unit_id))
			return false;

		return $this->getCachedResult("isViewer_$unit_id", function () use ($unit_id) {
			$local_acl = $this->userVipRepository->findOneAclByUIDModuleAndDataNum(
				$this->userEntity->getMain()['UID'],
				$this->getViewerModuleName(),
				$unit_id
			);

			return ($local_acl > 0);
		});
	}

	public function determineCompaniesForSubAdmin(): array
	{
		$vips = $this->userVipRepository->findAllActiveDataNumsByUIDModule(
			$this->userEntity->getMain()['UID'],
			$this->getSubAdminUserModuleName()
		);

		return array_column($vips, 'data_num');
	}

	/**
	 * @throws CoreException
	 */
	protected function hasGlobalAcl(string $aclName): bool
	{
		$value = $this->config->getConfigValue($aclName, $this->moduleName, self::GLOBAL_ACLS);
		return $this->validateAcl($value);
	}

	protected function getCachedResult(string $key, callable $callback): mixed
	{
		if (array_key_exists($key, $this->cache))
			return $this->cache[$key];

		$this->cache[$key] = $callback();
		return $this->cache[$key];
	}

	protected function validateAcl(string $acl_constant): bool
	{
		$acls = $this->userEntity->getAcl();
		return isset($acls[$this->moduleName]) && $acls[$this->moduleName] === $acl_constant;
	}
}