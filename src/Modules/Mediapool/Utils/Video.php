<?php
namespace App\Modules\Mediapool\Utils;


use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Ffmpeg;
use Imagick;
use ImagickException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;

class Video extends AbstractMediaHandler
{
	private Imagick $imagick;
	private Ffmpeg $ffmpeg;
	private int $maxVideoSize;

	/**
	 * @throws CoreException
	 */
	public function __construct(Config $config, Filesystem $fileSystem, Ffmpeg $ffmpeg, Imagick $imagick)
	{
		parent::__construct($config, $fileSystem); // should be first

		$this->ffmpeg       = $ffmpeg;
		$this->imagick      = $imagick;
		$this->maxVideoSize = $this->config->getConfigValue('videos', 'mediapool', 'max_file_sizes');
	}

	/**
	 * @throws ModuleException
	 */
	public function checkFileBeforeUpload(int $size): void
	{
		if ($size > $this->maxVideoSize)
			throw new ModuleException('mediapool', 'Filesize: '.$this->calculateToMegaByte($size).' MB exceeds max image size.');	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws FilesystemException
	 * @throws FrameworkException
	 */
	public function checkFileAfterUpload(string $filePath): void
	{
		if (!$this->filesystem->fileExists($filePath))
			throw new ModuleException('mediapool', 'After Upload Check: '.$filePath.' not exists.');

		$this->fileSize = $this->filesystem->fileSize($filePath);
		if ($this->fileSize > $this->maxVideoSize)
			throw new ModuleException('mediapool', 'After Upload Check: '.$this->calculateToMegaByte($this->fileSize).' MB exceeds max image size.');

		$this->ffmpeg->init($filePath);
		$mediaProperties = $this->ffmpeg->getMediaProperties();
		if ($mediaProperties['width'] > $this->maxWidth)
			throw new ModuleException('mediapool', 'After Upload Check:  Video width '.$mediaProperties['width'].' exceeds maximum.');

		if ($mediaProperties['height'] > $this->maxHeight)
			throw new ModuleException('mediapool', 'After Upload Check:  Video height '.$mediaProperties['height'] .' exceeds maximum.');

		$this->dimensions = ['width' => $mediaProperties['width'] , 'height' => $mediaProperties['height'] ];
	}

	/**
	 * @throws FilesystemException
	 * @throws FrameworkException
	 * @throws ImagickException
	 */
	public function createThumbnail(string $filePath): void
	{
		$vidcapPath = $this->ffmpeg->createVidCap('/'.$this->originalPath);

		$fileInfo = pathinfo($filePath);
		$thumbPath = '/'.$this->thumbPath.'/'.$fileInfo['filename'].'.jpg';
		$this->filesystem->move($vidcapPath, $thumbPath);

		$absolutePath = $this->getAbsolutePath($thumbPath);
		$this->imagick->readImage($absolutePath);
		$this->imagick->thumbnailImage($this->thumbWidth, $this->thumbHeight, true);
		$this->imagick->writeImage($absolutePath);
	}
}