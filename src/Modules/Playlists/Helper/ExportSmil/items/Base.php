<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);

namespace App\Modules\Playlists\Helper\ExportSmil\items;

use App\Framework\Core\Config\Config;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Conditional;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Properties;
use App\Modules\Playlists\Helper\ExportSmil\Utils\Trigger;

abstract class Base implements ItemInterface
{
	const string MASTER_ID_PREFIX        = 'm';
	const string TABSTOPS_TAG       = "\t\t\t\t\t\t\t";
	const string TABSTOPS_PARAMETER = "\t\t\t\t\t\t\t\t";
	const string TABSTOPS_PRIORITY  = "\t\t\t\t";
	/** @var array<string,mixed>  */
	protected array $item = [];
	protected readonly Config $config;
	protected readonly Trigger $begin;
	protected readonly Trigger $end;
	protected readonly Conditional $conditional;

	protected bool $isMaster = false;
	protected string $trigger = '';
	protected Properties $properties;

	/**
	 * @param array<string,mixed> $item
	 */
	public function __construct(Config $config, array $item, Properties $properties, Trigger $begin, Trigger $end, Conditional $conditional)
	{
		$this->config     = $config;
		$this->item       = $item;
		$this->properties = $properties;
		$this->begin      = $begin;
		$this->end        = $end;
		$this->conditional = $conditional;
	}

	public function setIsMasterPlaylist(bool $is): static
	{
		$this->isMaster = $is;
		return $this;
	}

	protected function determineBeginEndTrigger(): string
	{
		$ret = '';
		if ($this->begin->hasTriggers())
			$ret .= 'begin="'.$this->begin->determineTrigger().'" ';

		if ($this->end->hasTriggers())
			$ret .= 'end="'.$this->end->determineTrigger().'" ';

		return $ret;
	}

	protected function collectAttributes(): string
	{
		return $this->insertXmlId().
			$this->conditional->determineExprAttribute().
			$this->determineBeginEndTrigger().
			$this->encodeItemNameForTitleTag();
	}

	protected function insertXmlId(): string
	{
		if (!$this->isMaster)
			return 'xml:id="'.$this->item['item_id'].'" ';

		return 'xml:id="'.self::MASTER_ID_PREFIX.$this->item['item_id'].'" ';
	}

	public function getExclusive(): string
	{
		if (!$this->begin->hasTriggers())
			return '';

		$this->trigger = $this->determineBeginEndTrigger();
		$ret           = self::TABSTOPS_PRIORITY.'<priorityClass>'."\n";
		$ret          .= $this->getSmilElementTag();
		$ret           .= self::TABSTOPS_PRIORITY.'</priorityClass>'."\n";
		$this->trigger = '';

		return $ret;
	}

	/**
	 * Ampersand (&) in title tag can cause an XML validation error
	 * Bug occurred in garlic-player Android Rewe 2021-09-17
	 */
	protected function encodeItemNameForTitleTag(): string
	{
		$title = htmlspecialchars($this->item['item_name'], ENT_XML1);
		return 'title="'.$title.'" ';
	}

	protected function determineDuration(): string
	{
		if ($this->item['item_duration'] > 0)
			return 'dur="'.$this->item['item_duration'].'s" ';

		return '';
	}
}