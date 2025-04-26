<?php

namespace App\Modules\Playlists;

// Todo create a base Class ContentHelper

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;

class ContentReader
{
	private string $playlistsPath;
	private readonly Filesystem $fileSystem;

	/**
	 * @throws CoreException
	 */
	public function __construct(Config $Config, Filesystem $fileSystem)
	{
		$this->playlistsPath = $Config->getConfigValue('path_playlists', 'playlists');
		$this->fileSystem = $fileSystem;
	}

	/**
	 * @throws FilesystemException
	 */
	public function loadPlaylistItems($playlistId): string
	{
		return $this->load($playlistId, 'items.smil');
	}

	/**
	 * @throws FilesystemException
	 */
	public function loadPlaylistPrefetch($playlistId): string
	{
		return $this->load($playlistId, 'prefetch.smil');
	}

	/**
	 * @throws FilesystemException
	 */
	public function loadPlaylistExclusive($playlistId): string
	{
		return $this->load($playlistId, 'exclusive.smil');
	}

	/**
	 * @throws FilesystemException
	 */
	private function load(int $playlistId, string $file): string
	{
		if ($playlistId == 0)
			return '';

		return $this->fileSystem->read($this->playlistsPath.'/'.$playlistId . '/'.$file);
	}
}