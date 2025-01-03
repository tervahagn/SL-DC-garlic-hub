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
use Imagick;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Http\Message\UploadedFileInterface;

class Widget extends AbstractMediaHandler
{
	private int $maxDownloadSize;
	private ZipFilesystemFactory $zipFilesystemFactory;
	private Imagick $imagick;


	/**
	 * @throws CoreException
	 */
	public function __construct(Config $config, Filesystem $fileSystem, ZipFilesystemFactory $zipFilesystemFactory, Imagick $imagick)
	{
		parent::__construct($config, $fileSystem); // should be first

		$this->zipFilesystemFactory = $zipFilesystemFactory;
		$this->imagick              = $imagick;
		$this->maxDownloadSize      = $this->config->getConfigValue('download', 'mediapool', 'max_file_sizes');
	}

	/**
	 * @throws ModuleException
	 */
	public function checkFileBeforeUpload(UploadedFileInterface $uploadedFile): void
	{
		if ($uploadedFile->getError() !== UPLOAD_ERR_OK)
			throw new ModuleException('mediapool', $this->codeToMessage($uploadedFile->getError()));

		$size = (int) $uploadedFile->getSize();
		if ($size > $this->maxDownloadSize)
			throw new ModuleException('mediapool', 'Filesize: '.$this->calculateToMegaByte($size).' MB exceeds max download size.');
	}

	/**
	 * @throws ModuleException
	 * @throws FilesystemException
	 */
	public function checkFileAfterUpload(string $filePath): void
	{
		if (!$this->filesystem->fileExists($filePath))
			throw new ModuleException('mediapool', 'After Upload Check: '.$filePath.' not exists.');

		$this->fileSize = $this->filesystem->fileSize($filePath);
		if ($this->fileSize > $this->maxDownloadSize)
			throw new ModuleException('mediapool', 'After Upload Check: '.$this->calculateToMegaByte($this->fileSize).' MB exceeds max download size.');
	}

	/**
	 * @throws \ImagickException
	 * @throws FilesystemException
	 */
	public function createThumbnail(string $filePath): void
	{
		$zipFilesystem = $this->zipFilesystemFactory->create($filePath);

		if ($zipFilesystem->fileExists('icon.png'))
		{
			$imageContent = $zipFilesystem->read('icon.png');
			$this->imagick->readImageBlob($imageContent);
			$this->imagick->thumbnailImage($this->thumbWidth, $this->thumbHeight, true);

			$fileInfo = pathinfo($filePath);
			$thumbPath = $this->config->getPaths('systemDir').'/'.$this->thumbPath.'/'.$fileInfo['filename']. '.jpg';
			$this->imagick->writeImage($thumbPath);
		}
	}
}