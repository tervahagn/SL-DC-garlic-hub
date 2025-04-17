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

/**
 * Class to export SMIL media
 */
class Media extends Base implements ItemInterface
{
	const MEDIA_TYPE_IMAGE       = 'image';
	const MEDIA_TYPE_VIDEO       = 'video';
	const MEDIA_TYPE_AUDIO       = 'audio';
	const MEDIA_TYPE_WIDGET      = 'widget';
	const MEDIA_TYPE_DOWNLOAD    = 'download';
	const MEDIA_TYPE_APPLICATION = 'application';
	const MEDIA_TYPE_TEXT        = 'text';

	const MEDIA_ID_PREFIX        = 'm';

	const FITTING_FILL          = 1;
	const FITTING_MEET          = 2;
	const FITTING_MEETBEST      = 3;
	const FITTING_SLICE         = 4;
	const FITTING_SCROLL        = 5;

	protected $link = '';

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

	public function getElementForPreview(): string
	{
		switch($this->item['media_type'])
		{
			case self::MEDIA_TYPE_IMAGE:
				$media_link = ($this->item['item_type'] == ItemsModel::ITEM_TYPE_MEDIA) ? $this->link : './resources/icons/image.png';
				return '<img src="' . $media_link . '" dur="'.$this->item['item_duration'].'s" '.$this->getFit().' title="'.$this->item['item_name'].'" />'."\n";
			case self::MEDIA_TYPE_VIDEO:
				// check if intern media or external
				$media_link = ($this->item['item_type'] == ItemsModel::ITEM_TYPE_MEDIA) ? $this->link : './resources/icons/video.png';
				return '<video src="' . $media_link . '" soundLevel="'.$this->properties['volume'].'%"'.' '.$this->getFit().' title="'.$this->item['item_name'].'" />'."\n";

			case self::MEDIA_TYPE_AUDIO:
				// check if intern media or external
				$media_link = ($this->item['item_type'] == ItemsModel::ITEM_TYPE_MEDIA) ? $this->link : './resources/icons/audio.png';
				return '<audio src="' . $media_link . '" soundLevel="'.$this->properties['volume'].'%" title="'.$this->item['item_name'].'" />'."\n";

			case self::MEDIA_TYPE_WIDGET:
			case self::MEDIA_TYPE_DOWNLOAD:
			case self::MEDIA_TYPE_APPLICATION:
			case self::MEDIA_TYPE_TEXT:
				return '<img src="./resources/icons/html5.png" dur="'.$this->item['item_duration'].'s" '.$this->getFit().' title="'.$this->item['item_name'].'"/>'."\n";

			default:
				return '';
		}
	}

	public function getPrefetch(): string
	{
		$ret = '';
		if ($this->item['media_type'] != self::MEDIA_TYPE_TEXT) // to not set prefetch for websites
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
	public function setAudioTag()
	{
		$duration = '';
		if (($this->item['bearer'] == 1 AND $this->item['item_duration'] > 0))
			$duration = 'dur="'.$this->item['item_duration'].'s" ';
		$ret  = "\t\t\t\t\t\t\t".'<audio '.$this->insertXmlIdWhenMaster().$this->setExprAttribute().$this->trigger.'region="screen" src="'.$this->link.'" '.$duration.'soundLevel="'.$this->properties['volume'].'%" title="'.$this->encodeItemNameForTitleTag().'">'."\n";
		$ret .= $this->checkLoggable();
		if ($this->item['bearer'] == 0)
			$ret .= "\t\t\t\t\t\t\t\t".'<param name="cacheControl" value="onlyIfCached" />'."\n";
		elseif ($this->item['bearer'] == 1)
			$ret .= "\t\t\t\t\t\t\t\t".'<param name="stream" value="true" />'."\n";
		$ret .= "\t\t\t\t\t\t\t".'</audio>'."\n";
		return $ret;
	}

	/**
	 * @return string
	 */
	public function setVideoTag()
	{
		$duration = '';
		if (($this->item['bearer'] == 1 AND $this->item['item_duration'] > 0) OR $this->item['bearer'] == 2)
			$duration = 'dur="'.$this->item['item_duration'].'s" ';
		$ret  = "\t\t\t\t\t\t\t".'<video '.$this->insertXmlIdWhenMaster().$this->setExprAttribute().$this->trigger.'region="screen" src="'.$this->link.'" '.$duration.'soundLevel="'.$this->properties['volume'].'%"'.' '.$this->getFit().' title="'.$this->encodeItemNameForTitleTag().'">'."\n";
		if ($this->item['bearer'] == 2 AND $this->item['videoin_input'] != '')
			$ret .= "\t\t\t\t\t\t\t\t".'<param name="adapi:videoInput" value="'.$this->item['videoin_input'].'" />'."\n";
		$ret .= $this->checkLoggable();
		if ($this->item['bearer'] == 0)
			$ret .= "\t\t\t\t\t\t\t\t".'<param name="cacheControl" value="onlyIfCached" />'."\n";
		elseif ($this->item['bearer'] == 1)
			$ret .= "\t\t\t\t\t\t\t\t".'<param name="stream" value="true" />'."\n";
		$ret .= "\t\t\t\t\t\t\t".'</video>'."\n";
		return $ret;
	}

// ========================= protected methods ==============================================

	/**
	 * @return string
	 */
	protected function insertXmlIdWhenMaster()
	{
		if (!$this->is_master)
			return '';

		return 'xml:id="'.self::MEDIA_ID_PREFIX.$this->item['smil_playlist_item_id'].'" ';
	}

	/**
	 * @return string
	 */
	protected function selectTag()
	{
		switch($this->item['media_type'])
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
	 * @return string
	 */
	protected function getFit()
	{
		if (!array_key_exists('fit', $this->properties))
		{
			return '';
		}

		switch ($this->properties['fit'])
		{
			case self::FITTING_FILL:
				return 'fit="fill"';

			case self::FITTING_MEET:
				return 'fit="meet"';

			case self::FITTING_MEETBEST:
				return 'fit="meetBest"';

			case self::FITTING_SLICE:
				return 'fit="slice"';

			case self::FITTING_SCROLL:
				return 'fit="scroll"';

			default:
				return '';
		}
	}

	/**
	 * @return string
	 */
	protected function getMediaAlign()
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

	/**
	 * @return string
	 */
	protected function setParameters()
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

	/**
	 * @param   string  $tag
	 * @return  string
	 */
	protected function setCategories($tag)
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
	 * @return $this
	 */
	protected function getProperties(): static
	{
		if (!array_key_exists('properties', $this->item) || !is_array($this->item['properties']) || empty($this->item['properties']))
		{
			$this->item['properties'] = array(
				'transition' => $this->config->getConfigValue('_transition', 'smil_playlists'),
				'fit'        => $this->config->getConfigValue('_fit', 'smil_playlists'),
				'media_align'=> $this->config->getConfigValue('_media_align', 'smil_playlists'),
				'volume'     => $this->config->getConfigValue('_volume', 'smil_playlists'),
				'categories' => array(),
				'animation'  => array(
					'animate'        => 0,
					'attribute_name' => '',
					'from'           => 0,
					'to'             => 0,
					'begin'          => 0,
					'duration'       => 0
				)
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
		if ($this->item['loggable'] == 1)
			$ret =  "\t\t\t\t\t\t\t\t".'<param name="logContentId" value="'.$this->item['smil_playlist_item_id'].'" />'."\n";
		return $ret;
	}

}