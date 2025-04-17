<?php
namespace App\Modules\Playlists\Helper\ExportSmil\items;


use App\Framework\Exceptions\CoreException;

/**
 * Export channels to SMIL media
 */
class Channel extends Media implements ItemInterface
{

	public function getElement(): string
	{
		$element        = '';
	/*	$channel_server = $this->config->getConfigValue('_channel_server_url', 'channels');
		$path           = $this->config->getConfigValue('path_preview_images', 'channels');
		switch ($this->item['channel_type'])
		{
			case ChannelsModel::CHANNEL_TYPE_FEED:
				if ($this->item['loggable'] == 1)
					$element = '{CHANNEL_LIST_ITEMSLOGGABLE_'.$this->item['media_id'].'}'."\n";
				else
					$element = '{CHANNEL_LIST_ITEMS_'.$this->item['media_id'].'}'."\n";
				break;

			case ChannelsModel::CHANNEL_TYPE_WEATHER:
				if ($this->item['channel_view_mode'] != 1) // not single
					$this->setLink('{CHANNEL_GEO_'.$this->item['media_id'].'}');
				else // $this->item['media_id'].'_'.$this->item['media_id'] is needed because channels have keys regulary here channel_id == key
					$this->setLink($channel_server . $path . $this->item['media_id'].'_'.$this->item['media_id'].'.jpg');

				$this->ar_properties['fit'] = self::FITTING_MEET;
				$element = $this->setImageTag();
				break;

			case ChannelsModel::CHANNEL_TYPE_PLAYLIST:
				$element = '<seq title="'.$this->encodeItemNameForTitleTag().'">'."\n".'{ITEMS_'.$this->item['channel_table_id'].'}'."\n".'</seq>'."\n";
				break;

			case ChannelsModel::CHANNEL_TYPE_NO_SOURCE:
				$this->setLink($channel_server . $path . $this->item['media_id'].'.jpg');
				$element = $this->setImageTag();
				break;
		}
*/
		return $element;
	}

	public function getPrefetch(): string
	{
		$prefetch = '';
	/*	switch ($this->item['channel_type'])
		{
			case ChannelsModel::CHANNEL_TYPE_FEED:
					$prefetch = '{CHANNEL_LIST_PREFETCH_'.$this->item['media_id'].'}'."\n";
				break;

			case ChannelsModel::CHANNEL_TYPE_WEATHER:
				$prefetch = parent::getPrefetch();
				break;

			case ChannelsModel::CHANNEL_TYPE_NO_SOURCE:
				if ($this->item['channel_vendor'] == 'aponet_v21')
					$prefetch = '{CHANNEL_APONETV21_PREFETCH_'.$this->item['media_id'].'}'."\n";
				else
					$prefetch = parent::getPrefetch();
				break;

			case ChannelsModel::CHANNEL_TYPE_PLAYLIST:
				$prefetch = '{PREFETCH_'.$this->item['channel_table_id'].'}'."\n";
				break;

		}
	*/
		return $prefetch;
	}


	public function getElementForPreview(): string
	{
		$preview = '';
/*		if ($this->item['channel_type'] != ChannelsModel::CHANNEL_TYPE_PLAYLIST)
		{
			$channel_server = $this->config->getConfigValue('_channel_server_url', 'channels');
			$path           = $this->config->getConfigValue('path_preview_images', 'channels');

			$preview_path = $channel_server . $path . $this->item['media_id'] . '_preview.jpg';
			$preview = '<img src="'.$preview_path.'" dur="'.$this->item['item_duration'].'s" '.$this->getFit().' title="'.$this->encodeItemNameForTitleTag().'" />' . "\n";
		}
		else
		{
			$preview = '<seq id="playlist_'.$this->item['channel_table_id'].'" />'."\n";
		}
*/
		return $preview;
	}
}