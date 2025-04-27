<?php

namespace App\Modules\Player\IndexCreation\Builder;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Playlists\ContentReader;
use App\Modules\Playlists\ContentReaderExternal;
use App\Modules\Playlists\Helper\ExportSmil\items\Base;
use App\Modules\Playlists\Helper\PlaylistMode;
use Psr\Log\LoggerInterface;
use Throwable;

class PlaylistBuilder
{
	private readonly ContentReader $contentReader;
	private readonly ContentReaderExternal $contentReaderExternal;
	private PlayerEntity $playerEntity;
	private readonly LoggerInterface $logger;

	public function __construct(ContentReader $contentReader, ContentReaderExternal $contentReaderExternal,	LoggerInterface $logger)
	{
		$this->contentReader = $contentReader;
		$this->contentReaderExternal = $contentReaderExternal;
		$this->logger = $logger;
	}

	public function prepareSections(PlayerEntity $playerEntity): array
	{
		$this->playerEntity = $playerEntity;
		if ($this->playerEntity->getPlaylistMode() !== PlaylistMode::MULTIZONE->value)
			return $this->prepareNonMultiZone();
		else
			return $this->prepareMultiZone();
	}

	private function prepareNonMultiZone(): array
	{
		$ar_sections = $this->collectSections($this->playerEntity->getPlaylistId());

		$ar_sections['items'] = Base::TABSTOPS_TAG.'<seq repeatCount="indefinite">'. "\n" .
			$ar_sections['items'].Base::TABSTOPS_TAG.'</seq>' . "\n";

		return $ar_sections;
	}

	private function prepareMultiZone(): array
	{
		$items = '';
		$prefetch = '';
		$exclusive = '';

		foreach ($this->playerEntity->getZones() as $screen_id => $value)
		{
			$ar_sections = $this->collectSections($value['zone_smil_playlist_id']);

			$items .= Base::TABSTOPS_TAG . '<seq id="media' . $screen_id . '" repeatCount="indefinite">' . "\n" .
						str_replace('region="screen"','region="screen'.$screen_id.'"', $ar_sections['items']) .
						Base::TABSTOPS_TAG . '</seq>' . "\n";
			$prefetch  .= $ar_sections['prefetch'] . "\n";
			$exclusive .= str_replace('region="screen"', 'region="screen' . $screen_id . '"', $ar_sections['exclusive']);
		}

		return ['items' => $items, 'prefetch' => $prefetch, 'exclusive' => $exclusive];
	}

	private function collectSections(int $playlistId): array
	{
		return [
			'items' => $this->collectItems($playlistId),
			'prefetch' => $this->collectPrefetches($playlistId),
			'exclusive' => $this->collectExclusives($playlistId)
		];
	}

	private function collectItems($playlistId): string
	{
		try
		{
			$items = $this->contentReader->loadPlaylistItems($playlistId);
			$countMatch = preg_match_all('/{ITEMS_.*?}/', $items, $placeholders);
			if ($countMatch > 0)
			{
				foreach ($placeholders[0] as $value)
				{
					$subPlaylistId = $this->determineSubPlaylistIdFromPlaceholder($value);

					if ($subPlaylistId > 0)
					{
						$recurse = $this->collectItems($subPlaylistId);
						if (!empty($recurse))
							$items = str_replace('{ITEMS_'.$subPlaylistId.'}', "\n".$recurse."\n", $items);
						else
							$items = str_replace('{ITEMS_'.$subPlaylistId.'}', '', $items);
					}
					else
					{
						// get Playlist url from value after #
						$url          = $this->determineSubPlaylistUrlFromPlaceholder($value);
						$externalData = $this->contentReaderExternal->init($url)->loadPlaylistItems();
						$items        = str_replace('{ITEMS_0#'.$url.'}', $externalData, $items);
					}
				}
			}
		}
		catch (Throwable $e)
		{
			$this->logger->error('Error recurse items: ' . $e->getMessage());
			$items = '';
		}
		return $items;
	}

	private function collectPrefetches(int $playlistId): string
	{
		try
		{
			$prefetches = $this->contentReader->loadPlaylistPrefetch($playlistId);
			$countMatch = preg_match_all('/{PREFETCH_.*?}/', $prefetches, $placeholders); // check if there are any nested container with change greedines when more than one PREFETCH in one line happens
			if ($countMatch > 0)
			{
				foreach ($placeholders[0] as $placeholder)
				{
					$subPlaylistId = $this->determineSubPlaylistIdFromPlaceholder($placeholder);
					$recurse = $this->collectPrefetches($subPlaylistId);
					$prefetches = str_replace('{PREFETCH_' . $subPlaylistId . '}', $recurse, $prefetches);
				}
			}
		}
		catch (Throwable $e)
		{
			$this->logger->error('Error recurse prefetches: ' . $e->getMessage());
			$prefetches = '';
		}
		return $prefetches;
	}


	private function collectExclusives(int $playlistId, string $exclusive = ''): string
	{
		try
		{
			$exclusive .= $this->contentReader->loadPlaylistExclusive($playlistId);
			$countMatch = preg_match_all('/{ITEMS_.*}/', $exclusive, $placeholders);
			if ($countMatch > 0)
			{
				foreach ($placeholders[0] as $placeholder)
				{
					$subPlaylistId = $this->determineSubPlaylistIdFromPlaceholder($placeholder);
					$exclusive     = str_replace('{ITEMS_' . $subPlaylistId . '}', $this->contentReader->loadPlaylistItems($subPlaylistId), $exclusive);
					$exclusive     = $this->collectExclusives($subPlaylistId, $exclusive); // check if there is something nested
				}
			}
		}
		catch (Throwable $e)
		{
			$this->logger->error('Error recurse exclusive: ' . $e->getMessage());
			$exclusive = '';
		}

		return $exclusive;
	}

	private function determineSubPlaylistIdFromPlaceholder($placeholder): int
	{
		return (int) explode('_', $placeholder)[1];
	}

	private function determineSubPlaylistUrlFromPlaceholder($placeholder): string
	{
		$ar = explode('#', $placeholder);
		return trim($ar[1], '}');
	}
}