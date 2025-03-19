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
use App\Modules\Users\Services\UsersService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;

interface AbstractAclValidatorInterface
{
	const string SECTION_GLOBAL_ACLS = 'GlobalACLs';
	public function isModuleAdmin(int $UID): bool;

	public function isSubAdmin(int $UID): bool;

	public function isSimpleAdmin($UID): bool;

	public function isEditor(int $UID): bool;

	public function isViewer(int $UID): bool;
}