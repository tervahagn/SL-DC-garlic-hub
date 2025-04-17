<?php
namespace App\Modules\Playlists\Helper\ExportSmil;


use App\Framework\Core\Config\Config;
use App\Modules\Playlists\Helper\ExportSmil\items\Container;
use App\Modules\Playlists\Helper\ExportSmil\items\Media;
use Thymian\modules\playlists\items\Channel;
use Thymian\modules\playlists\items\Template;

class Content
{
	/**
	 * @var Media
	 */
	protected $Media;

	/**
	 * @var Container
	 */
	protected $Container;

	/**
	 * @var Template
	 */
	protected $Template;

	/**
	 * @var Channel
	 */
	protected $Channel;

	/**
	 * @var array
	 */
	protected $ar_archive_items = array();

	/**
	 * @var array
	 */
	protected $ar_media_symlinks = array();

	/**
	 * @var array
	 */
	protected $ar_templates_symlinks = array();

	/**
	 * @var string
	 */
	protected $export_base_path;

	/**
	 * @var string
	 */
	protected $content_elements;

	/**
	 * @var string
	 */
	protected $content_preview;

	/**
	 * @var string
	 */
	protected $content_prefetch;

	/**
	 * @var string
	 */
	protected $content_exclusive;

	/**
	 * @var int
	 */
	protected $playlist_id;

	/**
	 * @var int
	 */
	protected $playlist_mode;

	/**
	 * @var array
	 */
	protected $ar_items_data = array();


	public function __construct(Media $Media, Container $container, Template $template, Channel $Channel, Config $Config)
	{
		$this->setMedia($Media)
			 ->setContainer($container)
			 ->setTemplate($template)
			 ->setChannel($Channel)
			 ->setConfig($Config);
	}

	/**
	 * @param Media $Media
	 * @return $this
	 */
	public function setMedia(Media $Media)
	{
		$this->Media = $Media;
		return $this;
	}

	/**
	 * @return Media
	 */
	public function getMedia()
	{
		return $this->Media;
	}

	/**
	 * @param Container $container
	 * @return $this
	 */
	public function setContainer(Container $container)
	{
		$this->Container = $container;
		return $this;
	}

	/**
	 * @return Container
	 */
	public function getContainer()
	{
		return $this->Container;
	}

	/**
	 * @param Template $template
	 * @return $this
	 */
	public function setTemplate(Template $template)
	{
		$this->Template = $template;
		return $this;
	}

	/**
	 * @return Template
	 */
	public function getTemplate()
	{
		return $this->Template;
	}

	/**
	 * @param Channel $channel
	 * @return $this
	 */
	public function setChannel(Channel $channel)
	{
		$this->Channel = $channel;
		return $this;
	}

	/**
	 * @return Channel
	 */
	public function getChannel()
	{
		return $this->Channel;
	}

	/**
	 * @param $base_path
	 * @return $this
	 */
	public function setExportBasePath($base_path)
	{
		$this->export_base_path = $base_path;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getExportBasePath()
	{
		return $this->export_base_path;
	}

	/**
	 * @return array
	 */
	public function getMediaSymlinks()
	{
		return $this->ar_media_symlinks;
	}

	/**
	 * @param array $ar_symlinks
	 * @return $this
	 */
	public function setMediaSymlinks(array $ar_symlinks)
	{
		$this->ar_media_symlinks = $ar_symlinks;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getTemplatesSymlinks()
	{
		return $this->ar_templates_symlinks;
	}

	/**
	 * @param array $ar_symlinks
	 * @return $this
	 */
	public function setTemplatesSymlink(array $ar_symlinks)
	{
		$this->ar_templates_symlinks = $ar_symlinks;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPreviewContent()
	{
		return $this->content_preview;
	}

	/**
	 * @param   string  $content_preview
	 * @return  $this
	 */
	protected function addPreviewContent($content_preview)
	{
		$this->content_preview .= $content_preview;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPrefetchContent()
	{
		return $this->content_prefetch;
	}

	/**
	 * @param  string   $content_prefetch
	 * @return $this
	 */
	protected function addPrefetchContent($content_prefetch)
	{
		$this->content_prefetch .= $content_prefetch;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getExclusiveContent()
	{
		return $this->content_exclusive;
	}

	/**
	 * @param   string  $content_exclusive
	 * @return  $this
	 */
	protected function addExclusiveContent($content_exclusive)
	{
		$this->content_exclusive .= $content_exclusive;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getElementsContent()
	{
		return $this->content_elements;
	}

	/**
	 * @param   string  $content_elements
	 * @return  $this
	 */
	protected function addElementsContent($content_elements)
	{
		$this->content_elements .= $content_elements;
		return $this;
	}

	/**
	 * @param   int $playlist_id
	 * @return  $this
	 */
	public function setPlaylistId($playlist_id)
	{
		$this->playlist_id = (int) $playlist_id;
		return $this;
	}

	/**
	 * @param   int $playlist_id
	 * @return  $this
	 */
	public function setPlaylistMode($playlist_mode)
	{
		$this->playlist_mode = (int) $playlist_mode;
		return $this;
	}


	/**
	 * @return int
	 */
	public function getPlaylistId()
	{
		return $this->playlist_id;
	}

	/**
	 * @param array $ar_items_data
	 * @return $this
	 */
	public function setItemsData(array $ar_items_data)
	{
		$this->ar_items_data = $ar_items_data;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getItemsData()
	{
		return $this->ar_items_data;
	}

	/**
	 * @return array
	 */
	public function getArchiveItems()
	{
		return $this->ar_archive_items;
	}

	/**
	 * @param array $item_data
	 * @return $this
	 */
	public function addArchiveItem(array $item_data)
	{
		$this->ar_archive_items[] = $item_data;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function init()
	{
		$this->ar_media_symlinks        = array();
		$this->ar_templates_symlinks    = array();
		$this->ar_archive_items         = array();
		return $this;
	}

	/**
	 * @return $this
	 * @throws ModuleException
	 */
	public function build()
	{
		$this->content_preview      = '';
		$this->content_elements     = '';
		$this->content_prefetch     = '';
		$this->content_exclusive    = '';

		foreach ($this->getItemsData() as $item)
		{
			if ($item['disabled'] == 0)
			{
				$this->addArchiveItem(array(
					'smil_playlist_item_id' => $item['smil_playlist_item_id'],
					'smil_playlist_id'      => $item['smil_playlist_id'],
					'item_type'             => $item['item_type'],
					'media_id'              => $item['media_id'],
					'media_type'            => $item['media_type'],
					'item_name'             => $item['item_name'],
					'symlinkname'           => $item['symlinkname'],
					'external_link'         => $item['external_link']
				));
			}

			switch ($item['item_type'])
			{
				case ItemsModel::ITEM_TYPE_MEDIA;
					$this->buildMedia($item);
					break;

				case ItemsModel::ITEM_TYPE_MEDIA_EXTERN:
					$this->buildMediaExternal($item);
					break;

				case ItemsModel::ITEM_TYPE_PLAYLIST:
					$this->buildPlaylist($item);
					break;

				case ItemsModel::ITEM_TYPE_PLAYLIST_EXTERN:
					$this->buildPlaylistExternal($item);
					break;

				case ItemsModel::ITEM_TYPE_TEMPLATE:
					$this->buildTemplate($item);
					break;

				case ItemsModel::ITEM_TYPE_CHANNEL:
					$this->buildChannel($item);
					break;

				default:
					throw new ModuleException($this->module_name, 'Unknown item type. Given: ' . $item['item_type'] . ' with item id: ' . $item['smil_playlist_item_id']);
			}
		}

		$this->addShuffle();
		$this->content_preview  = '<?xml version="1.0" encoding="utf-8" standalone="yes"?><smil><head></head><body>'."\n".$this->content_preview.'</body></smil>';
		return $this;
	}

	/**
	 * builds the elements for an internal media item
	 *
	 * @param array $item
	 * @return $this
	 */
	private function buildMedia(array $item)
	{
		$this->getMedia()->set($item);
		$this->getMedia()->isMasterPlaylist($this->playlist_mode === Model::PLAYLIST_MODE_MASTER);

		$link = $this->export_base_path
			   .$this->getPlaylistId()
			   .'/' .$item['symlinkname'] . '.' . $item['media_filetype'];

		// content_server_url plus path
		$this->getMedia()->setLink($this->getConfig()->getConfigValue('_content_server_url', 'mediapool') . $link);

		$content_exclusive          = $this->getMedia()->getExclusive();
		$content_element            = $this->getMedia()->getElement();
		$content_prefetch           = $this->getMedia()->getPrefetch();
		$this->ar_media_symlinks[] = array('original' => $item['media_id'].'.'.$item['media_filetype'], 'obfuscated' => $link);

		// for preview, we need another link to media (using our getmedia.php-script)
		$this->getMedia()->setLink($this->getConfig()->getConfigValue('_content_server_url', 'mediapool') . 'getmedia.php?media_id='.$item['media_id'].'&amp;size=preview&amp;type='.$item['media_filetype']);
		$content_preview            = $this->getMedia()->getElementForPreview();

		return $this->setContentParts($item, ItemsModel::ITEM_TYPE_MEDIA, $content_element, $content_prefetch, $content_exclusive, $content_preview);
	}

	/**
	 * builds the elements for an external media item
	 *
	 * @param array $item
	 * @return $this
	 */
	private function buildMediaExternal(array $item)
	{
		$this->getMedia()->set($item);
		$this->getMedia()->setLink(str_replace('&', '&amp;', $item['external_link']));
		$content_exclusive    = $this->getMedia()->getExclusive();
		$content_element      = $this->getMedia()->getElement();
		$content_prefetch     = $this->getMedia()->getPrefetch();
		$content_preview      = $this->getMedia()->getElementForPreview();

		return $this->setContentParts($item, ItemsModel::ITEM_TYPE_MEDIA_EXTERN, $content_element, $content_prefetch, $content_exclusive, $content_preview);
	}

	/**
	 * builds the elements for a playlist item
	 *
	 * @param array $item
	 * @return $this
	 */
	private function buildPlaylist(array $item)
	{
		$this->getContainer()->set($item);
		$content_exclusive   = $this->getContainer()->getExclusive();
		$content_element     = $this->getContainer()->getElement();
		$content_prefetch    = $this->getContainer()->getPrefetch();
		$content_preview     = $this->getContainer()->getElementForPreview();

		return $this->setContentParts($item, ItemsModel::ITEM_TYPE_PLAYLIST, $content_element, $content_prefetch, $content_exclusive, $content_preview);
	}

	/**
	 * builds the elements for a playlist item
	 *
	 * @param array $item
	 * @return $this
	 */
	private function buildPlaylistExternal(array $item)
	{
		$this->getContainer()->set($item);
		$content_exclusive   = '';
		$content_element     = $this->getContainer()->getElementLink();
		$content_prefetch    = '';
		$content_preview     = '';

		return $this->setContentParts($item, ItemsModel::ITEM_TYPE_PLAYLIST_EXTERN, $content_element, $content_prefetch, $content_exclusive, $content_preview);
	}


	/**
	 * builds the elements for an template item
	 *
	 * @param array $item
	 * @return $this
	 */
	private function buildTemplate(array $item)
	{
		$this->getTemplate()->set($item);
		$this->getTemplate()->setPlaylistPath($this->export_base_path.$this->getPlaylistId().'/'); // do do the link to media inside class
		$content_exclusive      = $this->getTemplate()->getExclusive();
		$content_element        = $this->getTemplate()->getElement();
		$content_prefetch       = $this->getTemplate()->getPrefetch();
		$content_preview        = $this->getTemplate()->getElementForPreview();
		$this->ar_templates_symlinks[] = array(
			'original'      => $item['media_id'].$this->getTemplate()->getExtension(),
			'obfuscated'    => $this->export_base_path.$this->getPlaylistId().'/'.$item['symlinkname'].$this->getTemplate()->getExtension()
		);

		return $this->setContentParts($item, ItemsModel::ITEM_TYPE_TEMPLATE, $content_element, $content_prefetch, $content_exclusive, $content_preview);
	}

	/**
	 * builds the elements for a channel item
	 *
	 * @param array $item
	 * @return $this
	 */
	private function buildChannel(array $item)
	{
		$this->getChannel()->set($item);
		$content_element  = $this->getChannel()->getElement();
		$content_prefetch = $this->getChannel()->getPrefetch();
		$content_preview  = $this->getChannel()->getElementForPreview();

		return $this->setContentParts($item, ItemsModel::ITEM_TYPE_CHANNEL, $content_element, $content_prefetch, '', $content_preview);
	}

	/**
	 * @return $this
	 */
	private function addShuffle()
	{
		$shuffle = $this->checkShuffle();
		$this->content_elements  = $shuffle . $this->content_elements;
		$this->content_preview   = $shuffle . $this->content_preview;
		return $this;
	}

	/**
	 * set the the shuffle and the picking number if necessary
	 *
	 * @return string
	 */
	private function checkShuffle()
	{
		if ($this->getPlaylistDataByKey('shuffle') == 0)
		{
			return '';
		}

		// make sure, that the picking value is always greater than enabled media
		// $count_enabled = $this->dbh->getOne('COUNT(smil_playlist_item_id)', $this->table_items, 'smil_playlist_id=' . $this->ar_playlist['smil_playlist_id'] . ' AND disabled=0');
		$count_enabled = $this->getItemsModel()->countEnabledItemsByPlaylistId($this->getPlaylistId());

		if ($count_enabled == 0)
		{
			return '';
		}
		// use the either $count or picking
		$picking = min($count_enabled, $this->getPlaylistDataByKey('shuffle_picking'));

		if ($picking == 0)
		{
			return "\t\t\t\t\t\t".'<metadata><meta name="adapi:pickingAlgorithm" content="shuffle"/></metadata>'."\n";
		}

		return   "\t\t\t\t\t\t".'<metadata>'."\n"
				."\t\t\t\t\t\t\t".'<meta name="adapi:pickingAlgorithm" content="shuffle"/>'."\n"
				."\t\t\t\t\t\t\t".'<meta name="adapi:pickingBehavior" content="pickN"/>'."\n"
				."\t\t\t\t\t\t\t".'<meta name="adapi:pickNumber" content="'.$picking.'"/>'."\n"
				."\t\t\t\t\t\t".'</metadata>'."\n";
	}

	/**
	 * this handles the feature, that we want sometimes disabled items in prefetch
	 * and sometimes not.
	 * see comments, where the cases are explained
	 * The default is: add prefetch if item is disabled, add all other parts, if not disabled
	 *
	 * @param array     $item
	 * @param string    $item_type
	 * @param string    $content_element
	 * @param string    $content_prefetch
	 * @param string    $content_exclusive
	 * @param string    $content_preview
	 * @return $this
	 */
	private function setContentParts(array $item, $item_type, $content_element, $content_prefetch, $content_exclusive, $content_preview)
	{
		$disabled = (int) $item['disabled'];

		switch ($item_type)
		{
			case ItemsModel::ITEM_TYPE_MEDIA:
			case ItemsModel::ITEM_TYPE_CHANNEL:
			case ItemsModel::ITEM_TYPE_PLAYLIST:
			case ItemsModel::ITEM_TYPE_PLAYLIST_EXTERN:
				$this->addPrefetchContent($content_prefetch);
				if ($disabled == 0)
				{
					$this->addElementsContent($content_element)
						 ->addExclusiveContent($content_exclusive)
						 ->addPreviewContent($content_preview);
				}
				break;

			case ItemsModel::ITEM_TYPE_MEDIA_EXTERN:
				// no streams in prefetch when they are disabled
				if ($disabled == 1 && $item['bearer'] == ItemsModel::BEARER_TYPE_STREAM)
				{
					return $this;
				}

				// no external websites in prefetch, when disabled
				// (usually the content_prefetch should be empty in this case anyway, just do make sure in future)
				if ($disabled == 1 && $item['media_type'] == ItemsModel::ITEM_MEDIA_TYPE_HTML)
				{
					return $this;
				}
				$this->addPrefetchContent($content_prefetch);
				if ($disabled == 0)
				{
					$this->addElementsContent($content_element)
						 ->addExclusiveContent($content_exclusive)
						 ->addPreviewContent($content_preview);
				}
			break;

			case ItemsModel::ITEM_TYPE_TEMPLATE:
				// dont export prefetch on templates, if template is HTML and save_format is HTML (not WGT) and item is disabled
				if ($disabled == 1 && $item['template_media_type'] == ItemsModel::ITEM_MEDIA_TYPE_HTML && $item['website_save_format'] == ContentModel::WEBSITE_SAVE_FORMAT_HTML)
				{
					return $this;
				}

				$this->addPrefetchContent($content_prefetch);

				if ($disabled == 0)
				{
					$this->addElementsContent($content_element)
						 ->addExclusiveContent($content_exclusive)
						 ->addPreviewContent($content_preview);
				}
			break;
		}

		return $this;
	}
}