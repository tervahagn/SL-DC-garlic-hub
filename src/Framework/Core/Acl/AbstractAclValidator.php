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
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;

abstract class AbstractAclValidator
{
	protected AclHelper $helper;
	protected string $moduleName;

	public function __construct(string $moduleName, AclHelper $helper)
	{
		$this->helper        = $helper;
		$this->moduleName    = $moduleName;
	}

	public function getModuleName(): string
	{
		return $this->moduleName;
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws Exception
	 */
	public function isModuleAdmin(int $UID): bool
	{
		return $this->helper->isModuleAdmin($UID, $this->moduleName);
	}

	public function getConfig(): Config
	{
		return $this->helper->getConfig();
	}
	/**
	 * @throws CoreException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function isSubAdmin(int $UID): bool
	{
		return $this->helper->isSubAdmin($UID, $this->moduleName);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function isSimpleAdmin($UID): bool
	{
		if ($this->helper->isModuleAdmin($UID, $this->moduleName) || $this->helper->isSubAdmin($UID, $this->moduleName))
			return true;

		return false;
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
		return $this->helper->isEditor($UID, $this->moduleName);
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
		return $this->helper->isViewer($UID, $this->moduleName);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 * @throws CoreException
	 */
	public function isSubAdminWithAccessOnCompany(int $UID, int $companyId): bool
	{
		return ($this->isSubAdmin($UID) && $this->helper->hasSubAdminAccessOnCompany($UID, $companyId, $this->moduleName));
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 * @throws CoreException
	 */
	public function isEditorWithAccessOnUnit($UID, int|string $unitId): bool
	{
		return ($this->isEditor($UID) && $this->helper->hasEditorAccessOnUnit($UID, $unitId, $this->moduleName));
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 * @throws CoreException
	 */
	public function isViewerWithAccessOnUnit(int $UID, int|string $unitId): bool
	{
		return ($this->isViewer($UID) && $this->helper->hasViewerAccessOnUnit($UID, $unitId, $this->moduleName));
	}
}