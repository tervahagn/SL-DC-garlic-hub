<?php
namespace App\Modules\Playlists\Helper\ExportSmil\items;

use App\Framework\Exceptions\CoreException;
use App\Modules\Playlists\Helper\ExportSmil\items\Media;

/**
 * Class to export Template media to SMIL media
 */
class Template extends Media implements ItemInterface
{
	private string $ext;

	protected string $playlist_path;

	public function getExclusive(): string
	{
		if (!$this->hasBeginTrigger())
			return '';

		$this->trigger = $this->determineBeginEndTrigger();
		$ret           = "\t\t\t".'<priorityClass>'."\n";
		$ret          .= $this->prepareMedia();
		$ret          .= "\t\t\t".'</priorityClass>'."\n";
		$this->trigger = '';

		return $this->setCategories($ret);
	}

	public function getElement(): string
	{
		if ($this->hasBeginTrigger())
			return '';

		return $this->setCategories($this->prepareMedia());
	}

	public function getExtension(): string
	{
		return $this->ext;
	}

	public function getElementForPreview(): string
	{
		$template_server_url = $this->config->getConfigValue('_template_content_server_url', 'templates');
		$link = $template_server_url . 'index.php?site=templates_show_image&amp;content_id=' . $this->item['media_id'] . '&amp;size=preview&amp;type=jpg';
		return '<img src="'.$link.'" dur="'.$this->item['item_duration'].'s" '.$this->getFit().' title="'.$this->encodeItemNameForTitleTag().'" />'."\n";
	}

	/**
	 * @param   string $playlist_path
	 * @return  $this
	 */
	public function setPlaylistPath($playlist_path): static
	{
		$this->playlist_path = $playlist_path;
		return $this;
	}

	/**
	 * @return string
	 * @throws CoreException
	 */
	protected function prepareMedia(): string
	{
		if ($this->item['template_media_type'] == 'html')
		{
			$this->ext = ($this->isHtmlWidget()) ? '.wgt' : '.html';
			$this->setLink($this->buildTemplateSymlinkPath());
			$element = ($this->isHtmlWidget()) ? $this->setRefTag('application/widget') : $this->setTextTag();
		}
		else
		{
			if ($this->item['template_filetype'] == 1)
				$this->ext = '.png';
			else
				$this->ext = '.jpg';
			$this->setLink($this->buildTemplateSymlinkPath());
			$element = $this->setImageTag();
		}
		return $element;
	}


	/**
	 * @throws CoreException
	 */
	private function buildTemplateSymlinkPath(): string
	{
		$template_server_url = $this->config->getConfigValue('_template_content_server_url', 'templates');
		return $template_server_url . $this->playlist_path . $this->item['symlinkname'] . $this->ext;
	}

	/**
	 * check if it is a WGT widget or plain HTML
	 * returns TRUE if this is a wgt (Widget)
	 */
	private function isHtmlWidget(): bool
	{
		return (array_key_exists('website_save_format', $this->item) &&
				$this->item['website_save_format'] == ContentModel::WEBSITE_SAVE_FORMAT_WGT);
	}
}