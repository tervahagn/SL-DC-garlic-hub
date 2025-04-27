<?php

namespace App\Modules\Playlists\Collector;

// Todo create a base Class ContentHelper

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Modules\Playlists\Collector\Contracts\ContentReaderInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;

class ContentReader implements ContentReaderInterface
{
	private string $playlistsPath;
	private readonly Filesystem $fileSystem;
	private int $playlistId;

	/**
	 * @throws CoreException
	 */
	public function __construct(Config $Config, Filesystem $fileSystem)
	{
		$this->playlistsPath = $Config->getConfigValue('path_playlists', 'playlists');
		$this->fileSystem = $fileSystem;
	}

	public function init(int|string $playlistId): static
	{
		$this->playlistId = (int) $playlistId;

		return $this;
	}

	/**
	 * @throws FilesystemException
	 */
	public function loadPlaylistItems(): string
	{
		return $this->load($this->playlistId, 'items.smil');
	}

	/**
	 * @throws FilesystemException
	 */
	public function loadPlaylistPrefetch(): string
	{
		return $this->load($this->playlistId, 'prefetch.smil');
	}

	/**
	 * @throws FilesystemException
	 */
	public function loadPlaylistExclusive(): string
	{
		return $this->load($this->playlistId, 'exclusive.smil');
	}

	/**
	 * @throws FilesystemException
	 */
	private function load(string $file): string
	{
		if ($this->playlistId == 0)
			return '';

		return $this->fileSystem->read($this->playlistsPath.'/'.$this->playlistId . '/'.$file);
	}
}