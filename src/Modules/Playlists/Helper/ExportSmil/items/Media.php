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

use App\Framework\Exceptions\CoreException;
use App\Modules\Playlists\Helper\ItemDatasource;
use App\Modules\Playlists\Helper\ItemFlags;

/**
 * Class to export SMIL media
 */
class Media extends Base implements ItemInterface
{
	const string MEDIA_TYPE_IMAGE       = 'image';
	const string MEDIA_TYPE_VIDEO       = 'video';
	const string MEDIA_TYPE_AUDIO       = 'audio';
	const string MEDIA_TYPE_WIDGET      = 'widget';
	const string MEDIA_TYPE_DOWNLOAD    = 'download';
	const string MEDIA_TYPE_APPLICATION = 'application';
	const string MEDIA_TYPE_TEXT        = 'text';

	const string MEDIA_ID_PREFIX        = 'm';

	protected string $link = '';

	public function setLink($link):static
	{
		$this->link = $link;

		return $this;
	}

	public function getExclusive(): string
	{
		if (!$this->hasBeginTrigger())
			return '';

		$this->trigger = $this->determineBeginEndTrigger();
		$ret           = "\t\t\t".'<priorityClass>'."\n";
		$ret          .= $this->selectTag();
		$ret           .= "\t\t\t".'</priorityClass>'."\n";
		$this->trigger = '';

		return $this->setCategories($ret);
	}

	public function getElement(): string
	{
		if ($this->hasBeginTrigger())
			return '';

		return $this->setCategories($this->selectTag());
	}

	public function getPrefetch(): string
	{
		$ret = '';
		if ($this->item['mimetype'] !== 'text/html') // to not set prefetch for websites
			$ret = $this->setCategories("\t\t\t\t\t\t\t".'<prefetch src="'.$this->link.'" />'."\n");

		return $ret;
	}

	public function setImageTag(): string
	{
		$ret  =  "\t\t\t\t\t\t\t".
			'<img '.$this->insertXmlIdWhenMaster().$this->setExprAttribute().$this->trigger.
			'region="screen" src="'.$this->link.'" dur="'.$this->item['item_duration'].'s" '.
			$this->getFit().
			$this->getMediaAlign().
			' title="'.$this->encodeItemNameForTitleTag().'">'."\n";
		$ret .= $this->checkAnimation();
		$ret .= $this->checkLoggable();
		$ret .=  "\t\t\t\t\t\t\t\t".'<param name="cacheControl" value="onlyIfCached" />'."\n";
		$ret .=  "\t\t\t\t\t\t\t".'</img>'."\n";

		return $ret;
	}

	public function setRefTag($type): string
	{
		$ret  =  "\t\t\t\t\t\t\t".
			'<ref '.$this->insertXmlIdWhenMaster().$this->setExprAttribute().$this->trigger.'region="screen" src="'.$this->link.'" dur="'.$this->item['item_duration'].'s" type="'.$type.'" title="'.$this->encodeItemNameForTitleTag().'">'."\n";
		$ret .= $this->checkLoggable();
		$ret .=  "\t\t\t\t\t\t\t\t".'<param name="cacheControl" value="onlyIfCached" />'."\n";
		$ret .= $this->setParameters();
		$ret .=  "\t\t\t\t\t\t\t".'</ref>'."\n";
		return $ret;
	}

	public function setTextTag()
	{
		$ret  =  "\t\t\t\t\t\t\t".'<ref '.$this->insertXmlIdWhenMaster().$this->setExprAttribute().$this->trigger.'region="screen" src="'.$this->link.'" dur="'.$this->item['item_duration'].'s" type="text/html" title="'.$this->encodeItemNameForTitleTag().'">'."\n";
		$ret .= $this->checkLoggable();
		$ret .=  "\t\t\t\t\t\t\t".'</ref>'."\n";
		return $ret;
	}

	/**
	 * @return string
	 */
	public function setAudioTag(): string
	{
		$duration = '';
		if (($this->item['datasource'] == ItemDatasource::FILE->value && $this->item['item_duration'] > 0))
			$duration = 'dur="'.$this->item['item_duration'].'s" ';
		$ret  = "\t\t\t\t\t\t\t".'<audio '.$this->insertXmlIdWhenMaster().$this->setExprAttribute().$this->trigger.'region="screen" src="'.$this->link.'" '.$duration.'soundLevel="'.$this->properties['volume'].'%" title="'.$this->encodeItemNameForTitleTag().'">'."\n";
		$ret .= $this->checkLoggable();

		if ($this->item['datasource'] == ItemDatasource::FILE->value)
			$ret .= "\t\t\t\t\t\t\t\t".'<param name="cacheControl" value="onlyIfCached" />'."\n";
		elseif ($this->item['datasource'] == ItemDatasource::STREAM->value)
			$ret .= "\t\t\t\t\t\t\t\t".'<param name="stream" value="true" />'."\n";
		$ret .= "\t\t\t\t\t\t\t".'</audio>'."\n";
		return $ret;
	}

	/**
	 * @return string
	 */
	public function setVideoTag(): string
	{
		$duration = '';
		if (($this->item['datasource'] == ItemDatasource::FILE->value && $this->item['item_duration'] > 0))
			$duration = 'dur="'.$this->item['item_duration'].'s" ';

		$ret  = "\t\t\t\t\t\t\t".'<video '.$this->insertXmlIdWhenMaster().$this->setExprAttribute().$this->trigger.'region="screen" src="'.$this->link.'" '.$duration.'soundLevel="'.$this->properties['volume'].'%"'.' '.$this->getFit().' title="'.$this->encodeItemNameForTitleTag().'">'."\n";

		/*
		if ($this->item['datasource'] == ItemDatasource::STREAM->value && $this->item['videoin_input'] != '')
			$ret .= "\t\t\t\t\t\t\t\t".'<param name="adapi:videoInput" value="'.$this->item['videoin_input'].'" />'."\n";
		*/

		$ret .= $this->checkLoggable();

		if ($this->item['datasource'] == ItemDatasource::FILE->value)
			$ret .= "\t\t\t\t\t\t\t\t".'<param name="cacheControl" value="onlyIfCached" />'."\n";
		elseif ($this->item['datasource'] == ItemDatasource::STREAM->value)
			$ret .= "\t\t\t\t\t\t\t\t".'<param name="stream" value="true" />'."\n";

		$ret .= "\t\t\t\t\t\t\t".'</video>'."\n";

		return $ret;
	}


	protected function insertXmlIdWhenMaster(): string
	{
		if (!$this->isMaster)
			return '';

		return 'xml:id="'.self::MEDIA_ID_PREFIX.$this->item['smil_playlist_item_id'].'" ';
	}

	/**
	 * @return string
	 */
	protected function selectTag(): string
	{
		$mediaType = explode('/', $this->item['mimetype'], 2)[0];

		switch($mediaType)
		{
			case self::MEDIA_TYPE_IMAGE:
				return $this->setImageTag();

			case self::MEDIA_TYPE_VIDEO:
				return $this->setVideoTag();

			case self::MEDIA_TYPE_AUDIO:
				return $this->setAudioTag();

			case self::MEDIA_TYPE_WIDGET:
			case self::MEDIA_TYPE_DOWNLOAD:
			case self::MEDIA_TYPE_APPLICATION:
				return $this->setRefTag('application/widget');

			case self::MEDIA_TYPE_TEXT:
				return $this->setTextTag();

			default:
				return '';
		}
	}

	/**
	 * @throws CoreException
	 */
	protected function getFit(): string
	{
		if (!isset($this->properties['fit']))
			return 'fit="'.$this->config->getConfigValue('fit', 'playlists', 'Defaults').'"';

		return 'fit="'.$this->properties['fit'].'"';
	}

	protected function getMediaAlign(): string
	{
		if (!array_key_exists('media_align', $this->properties))
		{
			return '';
		}

		$has_this = array('topLeft', 'topMid', 'topRight', 'midLeft', 'center', 'midRight', 'bottomLeft', 'bottomMid', 'bottomRight');

		if (in_array($this->properties['media_align'], $has_this))
			return ' mediaAlign="'.$this->properties['media_align'].'"';

		return '';
	}

	protected function setParameters(): string
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

	protected function setCategories($tag): string
	{
		if (array_key_exists('categories', $this->properties) &&
			is_array($this->properties['categories']) &&
			count($this->properties['categories']) > 0)
		{
			sort($this->properties['categories'], SORT_NUMERIC);
			$categories = implode(';', $this->properties['categories']);
			if (strlen($categories) > 0)
			{
				$begin = '<!-- begin_categories '.$categories.' -->'."\n";
				$end   = '<!-- end_categories '.$categories.' -->'."\n";
				$tag   = $begin.$tag.$end;
			}
		}
		return $tag;
	}

	/**
	 * @throws CoreException
	 */
	protected function getProperties(): static
	{
		if (empty($this->item['properties']) || !is_array($this->item['properties']))
		{
			$this->item['properties'] = array(
				'transition' => $this->config->getConfigValue('transition', 'playlists', 'Defaults'),
				'fit'        => $this->config->getConfigValue('fit', 'playlists', 'Defaults'),
				'media_align'=> $this->config->getConfigValue('media_align', 'playlists', 'Defaults'),
				'volume'     => $this->config->getConfigValue('volume', 'playlists', 'Defaults'),
				'categories' => [],
				'animation'  => [
					'animate'        => 0,
					'attribute_name' => '',
					'from'           => 0,
					'to'             => 0,
					'begin'          => 0,
					'duration'       => 0
				]
			);
		}

		$this->properties = $this->item['properties'];

		return $this;
	}

	private function checkAnimation(): string
	{
		$ret = '';

		if (array_key_exists('animation', $this->properties) &&
			is_array($this->properties['animation']) &&
			$this->properties['animation']['animate'] == 1)
		{
			$ret =  "\t\t\t\t\t\t\t\t".'<animate attributeName="'.$this->properties['animation']['attribute_name'].'" from="'.$this->properties['animation']['from'].'%" to="'.$this->properties['animation']['to'].'%" begin="'.$this->properties['animation']['begin'].'s" dur="'.$this->properties['animation']['duration'].'s" fill="freeze" />'."\n";
		}
		return $ret;
	}

	private function checkLoggable(): string
	{
		$ret = '';
		if (($this->item['flags'] & ItemFlags::loggable->value) > 0)
			$ret =  "\t\t\t\t\t\t\t\t".'<param name="logContentId" value="'.$this->item['smil_playlist_item_id'].'" />'."\n";
		return $ret;
	}

	public function getElementForPreview()
	{
		// TODO: Implement getElementForPreview() method.
	}
}