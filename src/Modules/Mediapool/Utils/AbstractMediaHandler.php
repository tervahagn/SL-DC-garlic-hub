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
use App\Framework\Exceptions\ModuleException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\UploadedFile;

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
	protected string $previewPath;
	protected array $dimensions = [];
	protected int $fileSize;

	/**
	 * @param Config     $config
	 * @param Filesystem $filesystem
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
	}

	public function getDimensions(): array
	{
		return $this->dimensions;
	}

	public function getFileSize(): int
	{
		return $this->fileSize;
	}

	public function exists(string $filePath): bool
	{
		return $this->filesystem->fileExists($filePath);
	}

	abstract public function checkFileBeforeUpload(UploadedFileInterface $uploadedFile): void;
	abstract public function checkFileAfterUpload(string $filePath): void;
	abstract public function createThumbnail(string $filePath);

	public function upload(UploadedFileInterface $uploadedFile): string
	{
		$targetPath = strtolower('/'. $this->originalPath .'/'. $uploadedFile->getClientFilename());
		$uploadedFile->moveTo($this->getAbsolutePath($targetPath));

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

	public function determineNewFilePath(string $oldFilePath, string $filehash): string
	{
		$fileInfo    = pathinfo($oldFilePath);
		return $fileInfo['dirname']. '/'.$filehash.'.'.$fileInfo['extension'];
	}

	public function removeUploadedFile(string $filePath): void
	{
		$this->filesystem->delete($filePath);
	}

	public function rename(string $oldFilePath, string $newFilePath): void
	{
		$this->filesystem->move($oldFilePath, $newFilePath);
	}

	public function getAbsolutePath(string $filePath): string
	{
		return $this->config->getPaths('systemDir') . $filePath;
	}

	protected function codeToMessage(int $code): string
	{
		return match ($code)
		{
			UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
			UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
			UPLOAD_ERR_PARTIAL    => "The uploaded file was only partially uploaded",
			UPLOAD_ERR_NO_FILE    => "No file was uploaded",
			UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
			UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
			UPLOAD_ERR_EXTENSION  => "File upload stopped by extension",
			default => "Unknown upload error",
		};
	}

	protected function calculateToMegaByte(int $bytes): float
	{
		return round($bytes / (1024 ** 2), 2);
	}

}