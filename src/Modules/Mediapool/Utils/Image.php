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
use ImagickException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class Image provides methods to handle image files
 *
 * We use flysystem for file operations with systemDir as base
 * and Intervention image for image manipulations
 *
 * Be aware that flysystem requires "relative" path while
 * intervention image expects an absolute path.
 *
 */
class Image extends AbstractMediaHandler
{
	private Imagick $imagick;
	private int $maxImageSize;

	/**
	 * @throws CoreException
	 */
	public function __construct(Config $config, Filesystem $fileSystem, Imagick $imagick)
	{
		parent::__construct($config, $fileSystem); // should be first

		$this->imagick      = $imagick;
		$this->maxImageSize = $this->config->getConfigValue('images', 'mediapool', 'max_file_sizes');
	}

	/**
	 * @throws ModuleException
	 */
	public function checkFileBeforeUpload(UploadedFileInterface $uploadedFile): void
	{
		if ($uploadedFile->getError() !== UPLOAD_ERR_OK)
			throw new ModuleException('mediapool', $this->codeToMessage($uploadedFile->getError()));

		$size = (int) $uploadedFile->getSize();
		if ($size > $this->maxImageSize)
			throw new ModuleException('mediapool', 'Filesize: '.$this->calculateToMegaByte($size).' MB exceeds max image size.');
	}

	/**
	 * @throws ModuleException
	 * @throws FilesystemException
	 * @throws ImagickException
	 */
	public function checkFileAfterUpload(string $filePath): void
	{
		if (!$this->filesystem->fileExists($filePath))
			throw new ModuleException('mediapool', 'After Upload Check: '.$filePath.' not exists.');

		$this->fileSize = $this->filesystem->fileSize($filePath);
		if ($this->fileSize > $this->maxImageSize)
			throw new ModuleException('mediapool', 'After Upload Check: '.$this->calculateToMegaByte($this->fileSize).' MB exceeds max image size.');

		$this->imagick->readImage($this->getAbsolutePath($filePath));
		if ($this->imagick->getImageWidth() > $this->maxWidth)
			throw new ModuleException('mediapool', 'After Upload Check:  Image width '.$this->imagick->getImageWidth().' exceeds maximum.');

		if ($this->imagick->getImageHeight() > $this->maxHeight)
			throw new ModuleException('mediapool', 'After Upload Check:  Image height '.$this->imagick->getImageHeight().' exceeds maximum.');

		$this->dimensions = ['width' => $this->imagick->getImageWidth(), 'height' => $this->imagick->getImageHeight()];
	}

	/**
	 * @throws ImagickException
	 */
	public function createThumbnail(string $filePath): void
	{
		$this->imagick->thumbnailImage($this->thumbWidth, $this->thumbHeight, true);

		$fileInfo             = pathinfo($filePath);
		$thumbPath            = $this->config->getPaths('systemDir').'/'.$this->thumbPath.'/'.$fileInfo['basename'];
		$this->thumbExtension = $fileInfo['extension'];

		$this->imagick->writeImage($thumbPath);
	}


}