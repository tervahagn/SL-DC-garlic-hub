<?php
namespace App\Modules\Playlists\Helper\ExportSmil;


use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Helper\ExportSmil\items\Base;
use App\Modules\Playlists\Helper\ExportSmil\items\ItemsFactory;
use App\Modules\Playlists\Helper\ExportSmil\items\Media;
use App\Modules\Playlists\Helper\ExportSmil\items\SeqContainer;
use App\Modules\Playlists\Helper\ItemDatasource;
use App\Modules\Playlists\Helper\ItemFlags;
use App\Modules\Playlists\Helper\ItemType;
use App\Modules\Playlists\Helper\PlaylistMode;
use App\Modules\Playlists\Services\InsertItems\Playlist;

class PlaylistContent
{
	private readonly ItemsFactory $itemsFactory;
	private readonly Config $config;
	private string $contentElements;
	private string $contentPrefetch;
	private string $contentExclusive;
	private int $countEnabled = 0;
	private array $playlist  = [];
	private array $items     = [];

	public function __construct(ItemsFactory $itemsFactory, Config $config)
	{
		$this->itemsFactory     = $itemsFactory;
		$this->config           = $config;
	}

	public function init(array $playlist, array $items): static
	{
		$this->playlist     = $playlist;
		$this->items        = $items;
		$this->countEnabled = 0;
		$this->contentElements     = '';
		$this->contentPrefetch     = '';
		$this->contentExclusive    = '';

		return $this;
	}

	public function getContentElements(): string
	{
		return $this->contentElements;
	}

	public function getContentPrefetch(): string
	{
		return $this->contentPrefetch;
	}

	public function getContentExclusive(): string
	{
		return $this->contentExclusive;
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 */
	public function build(): static
	{
		foreach ($this->items as $item)
		{
			switch ($item['item_type'])
			{
				case ItemType::MEDIAPOOL->value;
					$this->buildMedia($item);
					break;

				case ItemType::MEDIA_EXTERN->value:
					$this->buildMediaExternal($item);
					break;

				case ItemType::PLAYLIST->value:
					$this->buildPlaylist($item);
					break;
/*
				case ItemType::PLAYLIST_EXTERN->value:
					$this->buildPlaylistExternal($item);
					break;

				case ItemType::TEMPLATE->value:
					$this->buildTemplate($item);
					break;

				case ItemType::CHANNEL->value:
					$this->buildChannel($item);
					break;
*/
				default:
					throw new ModuleException('playlists_items', 'Unknown item type. Given: ' . $item['item_type'] . ' with item id: ' . $item['item_id'].'.');
			}
		}

		$this->addShuffle();

		return $this;
	}

	/**
	 * @throws CoreException
	 * @throws ModuleException
	 */
	private function buildMedia(array $itemData): void
	{
		/** @var Media $item */
		$item = $this->itemsFactory->createItem($itemData);
		$item->setIsMasterPlaylist($this->playlist['playlist_mode'] === PlaylistMode::MASTER);

		$link = $this->config->getConfigValue('content_server_url', 'mediapool').'/'.
				str_replace('public/', '', $this->config->getConfigValue('originals', 'mediapool', 'directories')).'/'.
				$itemData['file_resource'].'.'. $itemData['extension'];

		$item->setLink($link);

		$this->addContentParts($itemData, $item->getSmilElementTag(), $item->getPrefetchTag(), $item->getExclusive());
	}

	/**
	 * @throws CoreException|ModuleException
	 */
	private function buildMediaExternal(array $itemData): void
	{
		/** @var Media $item */
		$item = $this->itemsFactory->createItem($itemData);
		$contentData = @unserialize($itemData['content_data']);
		$item->setLink(str_replace('&', '&amp;', $contentData['url']));

		$this->addContentParts($itemData, $item->getSmilElementTag(), $item->getPrefetchTag(), $item->getExclusive());
	}

	/**
	 * @throws CoreException|ModuleException
	 */
	private function buildPlaylist(array $itemData): void
	{
		/** @var SeqContainer $item */
		$item = $this->itemsFactory->createItem($itemData);

		$this->addContentParts($itemData, $item->getSmilElementTag(), $item->getPrefetchTag(), $item->getExclusive());
	}

/*
	private function buildPlaylistExternal(array $itemData): void
	{
		$item = $this->itemsFactory->createItem($itemData);

		$this->addContentParts($itemData, $item->getElementLink(), '', '');
	}

	private function buildTemplate(array $itemData): void
	{
		$item = $this->itemsFactory->createItem($itemData);
		$item->setPlaylistPath($this->export_base_path.$this->playlist['playlist_id'].'/'); // do the link to media inside class

		$this->addContentParts($itemData, $item->getSmilElementTag(), $item->getPrefetchTag(), $item->getExclusive());
	}

	private function buildChannel(array $itemData): void
	{
		$item = $this->itemsFactory->createItem($itemData);

		$this->addContentParts($itemData, $item->getSmilElementTag(), $item->getPrefetchTag(), $item->getExclusive());
	}
*/
	private function addShuffle(): void
	{
		if ($this->playlist['shuffle'] == 0 || $this->countEnabled == 0)
			return;

		// make sure, that the picking value is always <= than enabled media
		$picking = min($this->countEnabled, $this->playlist['shuffle_picking']);

		if ($picking == 0)
			$shuffle = Base::TABSTOPS_TAG.'<metadata><meta name="adapi:pickingAlgorithm" content="shuffle"/></metadata>'."\n";
		else
			$shuffle = Base::TABSTOPS_TAG.'<metadata>'."\n"
				.Base::TABSTOPS_PARAMETER.'<meta name="adapi:pickingAlgorithm" content="shuffle"/>'."\n"
				.Base::TABSTOPS_PARAMETER.'<meta name="adapi:pickingBehavior" content="pickN"/>'."\n"
				.Base::TABSTOPS_PARAMETER.'<meta name="adapi:pickNumber" content="'.$picking.'"/>'."\n"
				.Base::TABSTOPS_TAG.'</metadata>'."\n";

		$this->contentElements  = $shuffle . $this->contentElements;
	}

	/**
	 * this handles the feature, that we want sometimes disabled items in prefetch
	 * and sometimes not.
	 * see comments, where the cases are explained
	 * The default is: add prefetch if item is disabled, add all other parts, if not disabled
	 */
	private function addContentParts(array $item, string $element, string $prefetch, string $exclusive): void
	{
		$disabled = ($item['flags'] & ItemFlags::disabled->value) > 0;

		switch ($item['item_type'])
		{
			case ItemType::MEDIAPOOL->value:
//			case ItemType::CHANNEL->value:
			case ItemType::PLAYLIST->value:
//			case ItemType::PLAYLIST_EXTERN->value:
				$this->contentPrefetch .= $prefetch;
				break;

			case ItemType::MEDIA_EXTERN->value:
				// no streams or websites in prefetch
				if ($item['datasource'] !== ItemDatasource::STREAM->value && $item['mimetype'] !== 'text/html')
					$this->contentPrefetch .= $prefetch;

				break;
	/*		case ItemType::TEMPLATE->value:
				// don't export prefetch on templates, if template is HTML and save_format is HTML (not WGT)
				if ($item['template_mimetype'] != 'text/html' && $item['website_save_format'] !== 'html')
					$this->contentPrefetch .= $prefetch;
				break;
*/
			// default not required as we check in build method for valid ItemTypes and throw an exception
		}

		if (!$disabled)
		{
			$this->countEnabled++;
			$this->contentElements  .= $element;
			$this->contentExclusive .= $exclusive;
		}
	}
}