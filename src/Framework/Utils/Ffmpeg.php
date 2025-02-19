<?php
namespace App\Framework\Utils;


use App\Framework\Core\Config\Config;
use App\Framework\Core\ShellExecutor;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use stdClass;

/**
 * some convenient methods wrapping around ffmpeg functions
 *
 * The constructor only sets needed objects, but does not do anything
 *
 * Fore initialisation use the Ffmpeg::init($file_name) method.
 *
 * Ffmpeg::init() will take the video file name as an argument,
 * determine if it is local or on a remote server and call ffprobe (Ffmpeg::probeFile()) to read
 * metadata of the video and sets these data to the internal property Ffmpeg::media_properties = array()
 *
 * Class Ffmpeg
 */
class Ffmpeg
{

	private readonly Config $config;
	private readonly Filesystem $filesystem;
	private readonly ShellExecutor $shellExecutor;

	private string $probePath        = '/usr/bin/ffprobe';
	private string $executablePath   = '/usr/bin/ffmpeg';
	private string $scaleOptions     = '';
	private string $audioQuality     = '';
	private string $destinationFile  = '';
	private array $mediaProperties   = [];

	private array $metadata = [];
	private string $options;
	private float $duration;


	public function __construct(Config $config, Filesystem $fileSystem, ShellExecutor $shellExecutor)
	{
		$this->config        = $config;
		$this->filesystem    = $fileSystem;
		$this->shellExecutor = $shellExecutor;
	}

	public function setMetadata(array $metadata): void
	{
		$this->metadata = $metadata;
	}

	public function getMetadata(): array
	{
		return $this->metadata;
	}

	public function setMediaProperties(array $mediaProperties): void
	{
		$this->mediaProperties = $mediaProperties;
	}

	public function getMediaProperties(): array
	{
		return $this->mediaProperties;
	}

	public function getDuration(): float
	{
		return $this->duration;
	}

	/**
	 * need this init method, because we don't want to do this all in the constructor.
	 * This method should also clean up all internal properties for re-using with other videos
	 *
	 * @throws CoreException
	 * @throws FilesystemException|FrameworkException
	 */
	public function init(string $filePath): void
	{
		$this->setUpBinaryPathsByConfig()->setUpAudioQualityByConfig();

		$this->mediaProperties  = array();
		$this->options          = '';
		$this->scaleOptions     = '';
		$this->destinationFile  = '';

		if (!$this->filesystem->fileExists($filePath))
			throw new FrameworkException('File does not exist: ' . $filePath);

		$this->probeFile($this->getAbsolutePath($filePath));
	}

	/**
	 * @throws  FrameworkException
	 * @throws  Exception|FilesystemException
	 */
	public function createVidCap(string $destination): string
	{
		if ($this->mediaProperties['video_codec'] == '')
			throw new FrameworkException('Can create video captions of ' . $this->mediaProperties['filename'] . '. File has no readable video stream');

		$command = $this->executablePath . ' -i ' .
					$this->mediaProperties['filename'] .
					$this->scaleOptions .
					' -ss 00:00:02 -an -r 1 -vframes 1 -y '.$this->getAbsolutePath($destination).'/vid_%d.jpg 2>&1';

		$this->shellExecutor->setCommand($command);
		$this->shellExecutor->execute();

		$vidcapPath = $destination . '/vid_1.jpg';
		if (!$this->filesystem->fileExists($vidcapPath))
			throw new FrameworkException('Vid cap ' . $vidcapPath . ' not found');

		return $vidcapPath;
	}

	/**
	 * @throws CoreException
	 */
	private function setUpBinaryPathsByConfig(): static
	{
		$this->executablePath  = $this->config->getConfigValue('path_to_ffmpeg_bin', 'video');
		$this->probePath       = $this->config->getConfigValue('path_to_ffmpeg_probe', 'video');
		return $this;
	}

	/**
	 * @throws CoreException
	 */
	private function setUpAudioQualityByConfig(): void
	{
		$this->audioQuality = $this->config->getConfigValue('audio_quality_ffmpeg_param', 'video');
	}

	/**
	 * @throws  FrameworkException
	 * @throws CoreException
	 */
	private function probeFile(string $filePath): void
	{
		$command    = $this->probePath . ' -v quiet -print_format json -show_format -show_streams ' . $filePath;
		$this->shellExecutor->setCommand($command);
		$result     = $this->shellExecutor->executeSimple();
		$metadata   = json_decode($result);

		if (is_null($metadata) || !isset($metadata->format))
			throw new FrameworkException('Probing media file failed. Unsupported file type for file ' . $filePath . '. Using command: ' . $command);

		$this->parseMediaProperties($metadata);
	}

	private function parseMediaProperties(stdClass $meta_data): void
	{
		// init
		$this->mediaProperties = [
			'width'             => 0,
			'height'            => 0,
			'video_codec'       => '',
			'aspect_ratio'      => '',
			'audio_codec'       => ''
		];

		foreach ($meta_data->streams as $value)
		{
			if ($value->codec_type == 'video' && isset($value->codec_name))
			{
				$this->mediaProperties['video_codec']  = (string) $value->codec_name;

				// prepare for ffmpeg 4.1.x some wmv do not have a sample_aspect_ratio/display_aspect_ratio
				// ffmpeg 2.x there is no problem
				if (isset($value->display_aspect_ratio))
					$this->mediaProperties['aspect_ratio'] = (string) $value->display_aspect_ratio;
				else
					$this->mediaProperties['aspect_ratio'] = '1:1';

				$this->mediaProperties['width']  = (int) $value->width;
				$this->mediaProperties['height'] = (int) $value->height;
			}
			elseif ($value->codec_type == 'audio')
			{
				$this->mediaProperties['audio_codec'] = (string) $value->codec_name;
			}
		}

		// Because WebApi MediaRecorder does not include duration in the created webm files
		// we need to it from the WebUI metadata or set it to zero.
		if (isset($meta_data->format->duration))
			$duration = $meta_data->format->duration;
		else if (array_key_exists('duration', $this->metadata))
			$duration = (float) $this->metadata['duration'];
		else
			$duration = 0;

		$this->mediaProperties['start_time'] = (string) $meta_data->format->start_time;
		$this->mediaProperties['duration']   = (float) $duration;
		$this->duration                      = (float) $duration;
		$this->mediaProperties['filename']   = (string) $meta_data->format->filename;
		$this->mediaProperties['filesize']   = (int) $meta_data->format->size;
		$this->mediaProperties['container']  = (string) $meta_data->format->format_name;
	}


	private function getAbsolutePath(string $filePath): string
	{
		return $this->config->getPaths('systemDir') . $filePath;
	}

}