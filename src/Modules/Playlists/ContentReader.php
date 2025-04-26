<?php

namespace App\Modules\Playlists;

// Todo create a base Class ContentHelper


use App\Framework\Core\Config\Config;
use League\Flysystem\Filesystem;

class ContentReader
{
	protected string $playlistsPath = '';
	protected $fileSystem;

	public function __construct(Config $Config, Filesystem $fileSystem)
	{
		$this->playlistsPath = $Config->getConfigValue('path_playlists', 'playlists');
		$this->fileSystem = $fileSystem;
	}

	public function loadItems($playlist_id): string
	{
		if ($playlist_id == 0)
			return '';
		$path = $this->playlistsPath.$playlist_id . '/items.smil';
		return $this->loadFileContents($path);
	}

	public function loadPrefetch($playlist_id): string
	{
		$path = $this->playlistsPath.$playlist_id .  '/prefetch.smil';
		return $this->loadFileContents($path);
	}

	public function loadExclusive(int $playlist_id): string
	{
		$path = $this->playlistsPath.$playlist_id .  '/exclusive.smil';
		return $this->loadFileContents($path);
	}

	protected function loadFileContents($path)
	{
		return $this->fileSystem->read($path);
	}

}