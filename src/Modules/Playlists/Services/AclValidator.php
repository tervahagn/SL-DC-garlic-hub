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

namespace App\Modules\Playlists\Services;

use App\Framework\Core\Acl\AbstractAclValidator;
use App\Framework\Exceptions\CoreException;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;

class AclValidator extends AbstractAclValidator
{
	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function isPlaylistEditable(int $UID, array $playlist): bool
	{
		if ($this->isModuleAdmin($UID))
			return true;

		// Edge Edition will not move further as there is not subadmin
		if ($this->isSubAdmin($UID) && $this->hasSubAdminAccessOnCompany($UID, $playlist['company_id']))
			return true;

		if($this->isEditor($UID) && $this->hasEditorAccessOnUnit($UID, $playlist['playlist_id']))
			return true;

		if ($UID == $playlist['UID'])
			return true;

		return false;
	}

}