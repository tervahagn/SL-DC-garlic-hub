<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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


namespace App\Modules\Mediapool\Utils;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Http\Message\UploadedFileInterface;

abstract class AbstractMediaHandler
{
	protected Config $config;
	protected Filesystem $filesystem;
	protected int $thumbWidth;
	protected int $thumbHeight;
	protected int $maxWidth;
	protected int $maxHeight;
	protected string $thumbPath;
	protected string $uploadPath;
	protected string $originalPath;
	protected string $iconsPath;
	protected string $previewPath;
	protected array $dimensions = [];
	protected int $fileSize;
	protected string $thumbExtension = 'jpg';
	protected float $duration = 0.0;

	protected array $metadata = [];
	/**
	 * @param Config $config
	 * @param Filesystem $filesystem
	 * @throws CoreException
	 */
	public function __construct(Config $config, Filesystem $filesystem)
	{
		$this->config     = $config;
		$this->filesystem = $filesystem;

		$this->maxWidth   = $this->config->getConfigValue('width', 'mediapool', 'max_resolution');
		$this->maxHeight  = $this->config->getConfigValue('height', 'mediapool', 'max_resolution');

		$this->thumbWidth   = $this->config->getConfigValue('thumb_width', 'mediapool', 'dimensions');
		$this->thumbHeight  = $this->config->getConfigValue('thumb_height', 'mediapool', 'dimensions');
		$this->uploadPath   = $this->config->getConfigValue('uploads', 'mediapool', 'directories');
		$this->thumbPath    = $this->config->getConfigValue('thumbnails', 'mediapool', 'directories');
		$this->originalPath = $this->config->getConfigValue('originals', 'mediapool', 'directories');
		$this->previewPath  = $this->config->getConfigValue('previews', 'mediapool', 'directories');
		$this->iconsPath    = $this->config->getConfigValue('icons', 'mediapool', 'directories');
	}

	public function setMetadata(array $metadata): void
	{
		$this->metadata = $metadata;
	}

	public function getDimensions(): array
	{
		return $this->dimensions;
	}

	public function getDuration(): float
	{
		return $this->duration;
	}

	public function getThumbExtension(): string
	{
		return $this->thumbExtension;
	}

	public function getFileSize(): int
	{
		return $this->fileSize;
	}

	/**
	 * @throws FilesystemException
	 */
	public function exists(string $filePath): bool
	{
		return $this->filesystem->fileExists($filePath);
	}

	abstract public function checkFileBeforeUpload(int $size): void;
	abstract public function checkFileAfterUpload(string $filePath): void;
	abstract public function createThumbnail(string $filePath);

	public function uploadFromLocal(UploadedFileInterface $uploadedFile): string
	{
		$targetPath = strtolower('/'. $this->originalPath .'/'. $uploadedFile->getClientFilename());
		$uploadedFile->moveTo($this->getAbsolutePath($targetPath));

		return $targetPath;
	}

	/**
	 * @throws GuzzleException
	 */
	public function uploadFromExternal(Client $client, string $fileURI): string
	{
		$parsedUrl  = parse_url($fileURI);
		$pathInfo   = pathinfo($parsedUrl['path']);
		$targetPath = strtolower('/'. $this->originalPath .'/'. $pathInfo['basename']);

		$client->request('GET', $fileURI, ['sink' => $this->getAbsolutePath($targetPath)]);
		return $targetPath;
	}


	/**
	 * @throws FilesystemException
	 * @throws ModuleException
	 */
	public function determineNewFilename(string $filePath): string
	{
		if (!$this->filesystem->fileExists($filePath))
			throw new ModuleException('mediapool', 'Filesize: '.$filePath.' not exists');

		$stream = $this->filesystem->readStream($filePath);
		if (!$stream)
			throw new ModuleException('mediapool', 'Filesize: '.$filePath.' not readable');

		$hash = hash('sha256', stream_get_contents($stream));
		fclose($stream);

		return $hash;
	}

	public function determineNewFilePath(string $oldFilePath, string $filehash, string $mimeType): string
	{
		$fileInfo    = pathinfo($oldFilePath);

		// if files have no extension take it from mime type
		if (array_key_exists('extension', $fileInfo))
			$ext = $fileInfo['extension'];
		else
			$ext = explode('/', $mimeType)[1];

		// normalize jpeg to jpg
		$ext = str_replace('jpeg', 'jpg', $ext);

		return $fileInfo['dirname']. '/'.$filehash.'.'.$ext;
	}

	/**
	 * @throws FilesystemException
	 */
	public function removeUploadedFile(string $filePath): void
	{
		$this->filesystem->delete($filePath);
	}

	/**
	 * @throws FilesystemException
	 */
	public function rename(string $oldFilePath, string $newFilePath): void
	{
		$this->filesystem->move($oldFilePath, $newFilePath);
	}

	public function getAbsolutePath(string $filePath): string
	{
		return $this->config->getPaths('systemDir') . $filePath;
	}

	protected function calculateToMegaByte(int $bytes): float
	{
		return round($bytes / (1024 ** 2), 2);
	}

}