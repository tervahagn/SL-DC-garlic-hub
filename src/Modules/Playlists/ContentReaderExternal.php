<?php

namespace App\Modules\Playlists;



use App\Framework\Exceptions\ModuleException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Http\Message\ResponseInterface;

class ContentReaderExternal
{
	private string $cachePath;
	private FileSystem $fileSystem;
	private Client $client;
	private string $cachedFile = '';
	private ResponseInterface $response;
	private string $playlistLink;

	public function __construct(FileSystem $fileSystem, Client $client, string $cachePath)
	{
		$this->cachePath  = $cachePath;
		$this->client     = $client;
		$this->fileSystem = $fileSystem;
	}

	/**
	 * @throws GuzzleException
	 */
	public function init(string $playlistLink): static
	{
		$this->playlistLink = $playlistLink;
		$this->cachedFile   = $this->cachePath . '/' . md5($this->playlistLink) . '.smil';
		$this->response     = $this->client->head($this->playlistLink);

		return $this;
	}


	/**
	 * @throws ModuleException
	 * @throws FilesystemException
	 * @throws GuzzleException
	 */
	public function loadPlaylistItems(): string
	{
		if (!$this->fileSystem->fileExists($this->cachedFile))
			$this->handleFirstDownload();
		else
			$this->checkForRemoteUpdated();

		return $this->fileSystem->read($this->cachedFile);
	}

	/**
	 * @throws GuzzleException
	 * @throws FilesystemException
	 */
	private function checkForRemoteUpdated(): void
	{
		if ($this->response->getStatusCode() == 200 && $this->mustUpdate())
			$this->downloadRemoteFile();
	}

	/**
	 * @throws FilesystemException
	 */
	private function mustUpdate(): bool
	{
		return ($this->response->getHeaderLine('Last-Modified') > $this->fileSystem->lastModified($this->cachedFile) ||
			$this->response->getHeaderLine('Content-Length') != $this->fileSystem->fileSize($this->cachedFile))
		;
	}

	/**
	 * @throws ModuleException
	 * @throws GuzzleException
	 */
	private function handleFirstDownload(): void
	{
		if ($this->response->getStatusCode() != 200)
			throw new ModuleException('smil_index', 'Http-Code of '.$this->playlistLink.' is: '.$this->response->getStatusCode());

		$this->downloadRemoteFile();
	}

	/**
	 * @throws GuzzleException
	 */
	private function downloadRemoteFile(): void
	{
		$this->client->get($this->playlistLink, ['sink' => $this->cachedFile]);
	}

}