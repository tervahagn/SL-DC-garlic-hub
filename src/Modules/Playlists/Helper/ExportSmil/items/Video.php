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


namespace App\Modules\Playlists\Helper\ExportSmil\items;

use App\Modules\Playlists\Helper\ItemDatasource;

class Video extends Media
{

	public function getSmilElementTag(): string
	{
		$ret  = self::TABSTOPS_TAG.'<video '.$this->collectMediaAttributes().'">'."\n";
		$ret .= $this->collectParameters();
		$ret .= self::TABSTOPS_TAG.'</video>'."\n";

		return $ret;
	}

	protected function collectParameters(): string
	{
		$param = parent::collectParameters();

		if ($this->item['datasource'] == ItemDatasource::STREAM->value)
			$param .= self::TABSTOPS_PARAMETER.'<param name="stream" value="true" />'."\n";

		return $param;
	}

	protected function collectMediaAttributes(): string
	{
		return parent::collectMediaAttributes().' '.$this->properties->getVolume();
	}

}