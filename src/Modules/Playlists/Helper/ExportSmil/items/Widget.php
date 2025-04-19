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

class Widget extends Media
{

	public function getSmilElementTag(): string
	{
		$ret  =  "\t\t\t\t\t\t\t".'<ref '.$this->collectMediaAttributes().'>'."\n";
		$ret .= $this->collectParameters();
		$ret .=  "\t\t\t\t\t\t\t\t".'<param name="cacheControl" value="onlyIfCached" />'."\n";
		$ret .= $this->collectParameters();
		$ret .=  "\t\t\t\t\t\t\t".'</ref>'."\n";
		return $ret;
	}

	protected function collectMediaAttributes(): string
	{
		return parent::collectMediaAttributes().' type="application/widget"';
	}

	protected function collectParameters(): string
	{
		return parent::collectParameters().$this->collectWidgetParameters();
	}

	protected function collectWidgetParameters(): string
	{
		$ret = '';
		if (is_null($this->item['content_data'] || empty($this->item['content_data'])))
			return $ret;
		$parameters = unserialize($this->item['content_data']);
		if (!is_array($parameters))
			return $ret;

		foreach ($parameters as $key => $value)
		{
			$ret .=  "\t\t\t\t\t\t\t\t".'<param name="'.$key.'" value="'.$value.'" />'."\n";
		}
		return $ret;
	}
}