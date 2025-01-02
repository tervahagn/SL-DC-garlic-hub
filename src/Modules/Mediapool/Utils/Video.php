<?php
namespace App\Modules\Mediapool\Utils;


use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use Exception;
use Intervention\Image\Interfaces\ImageManagerInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Http\Message\UploadedFileInterface;
use stdClass;

/**
 * some convenient methods wrapping around ffmpeg functions
 *
 * The constructor only sets needed objects, but does not do anything
 *
 * Fore initialisation use the Ffmpeg::init($file_name) method.
 *
 * Ffmpeg::init() will take the video file name as a argument,
 * determine if it is local or on a remote server and call ffprobe (Ffmpeg::probeFile()) to read
 * meta data of the video and sets these data to the internal property Ffmpeg::media_properties = array()
 *
 * Class Ffmpeg
 *
 * @package Thymian\framework\utils\video
 */
class Video extends AbstractMediaHandler
{

	protected string $probePath        = '/usr/bin/ffprobe';
	protected string $executablePath   = '/usr/bin/ffmpeg';
	protected string $options          = '';
	protected string $scaleOptions     = '';
	protected string $audioQuality     = '';
	protected string $destinationFile  = '';
	protected array $mediaProperties   = [];
	private ImageManagerInterface $imageManager;
	private int $maxVideoSize;

	/**
	 * @throws CoreException
	 */
	public function __construct(Config $config, Filesystem $fileSystem, ImageManagerInterface $imageManager)
	{
		parent::__construct($config, $fileSystem); // should be first

		$this->imageManager = $imageManager;
		$this->maxVideoSize = $this->config->getConfigValue('images', 'mediapool', 'max_file_sizes');
	}

	/**
	 * @throws ModuleException
	 */
	public function checkFileBeforeUpload(UploadedFileInterface $uploadedFile): void
	{
		if ($uploadedFile->getError() !== UPLOAD_ERR_OK)
			throw new ModuleException('mediapool', $this->codeToMessage($uploadedFile->getError()));

		$size = (int) $uploadedFile->getSize();
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

		$this->init($filePath);
		if ($this->mediaProperties['width'] > $this->maxWidth)
			throw new ModuleException('mediapool', 'After Upload Check:  Video width '.$this->mediaProperties['width'].' exceeds maximum.');

		if ($this->mediaProperties['height'] > $this->maxHeight)
			throw new ModuleException('mediapool', 'After Upload Check:  Video height '.$this->mediaProperties['height'] .' exceeds maximum.');

		$this->dimensions = ['width' => $this->mediaProperties['width'] , 'height' => $this->mediaProperties['height'] ];
	}

	/**
	 * @throws FilesystemException
	 * @throws FrameworkException
	 */
	public function createThumbnail(string $filePath): void
	{
		$vidcapPath = $this->createVidCap('/'.$this->originalPath);

		$fileInfo = pathinfo($filePath);
		$thumbPath = '/'.$this->thumbPath.'/'.$fileInfo['filename'].'.jpg';
		$this->filesystem->move($vidcapPath, $thumbPath);

		$absolutePath = $this->getAbsolutePath($thumbPath);
		$image = $this->imageManager->read($absolutePath);
		$image->scaleDown($this->thumbWidth, $this->thumbHeight);

		$image->save($absolutePath);

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

	public function saveAsTheora(string $destination): static
	{
		$this->options = $this->setVideoCodec('libtheora');

		if ($this->mediaProperties['audio_codec'] != '')
			$this->options .= ' -acodec libvorbis' . $this->audioQuality;

		$this->destinationFile = $destination . '.ogg';
		return $this;
	}

	public function saveAsWebM(string $destination): static
	{
		$this->options = $this->setVideoCodec('libvpx');

		if ($this->mediaProperties['audio_codec'] != '')
			$this->options .= ' -acodec libvorbis' . $this->audioQuality;

		$this->destinationFile = $destination . '.webm';
		return $this;
	}

	public function saveAsH264AVI(string $destination): static
	{
		$this->options     = $this->setVideoCodec('h264') . ' -r 25 -g 12 -keyint_min 2 -profile:v high -pix_fmt yuv420p';

		if ($this->mediaProperties['audio_codec'] != '')
			$this->options    .= ' -acodec ac3' . $this->audioQuality;

		$this->destinationFile = $destination . '.avi';
		return $this;
	}

	public function saveAsH264MP4(string $destination): static
	{
		$this->options     = $this->setVideoCodec('h264');//.' -r 25 -g 12 -keyint_min 2 -profile:v high -pix_fmt yuv420p';

		// there is an error if with cannot be divide by 2
		if ($this->mediaProperties['height']%2 != 0 || $this->mediaProperties['width']%2 != 0)
		{
			$width  = $this->mediaProperties['width'];
			$height = $this->mediaProperties['height'];

			if ($width %2 != 0 )
				$width ++;

			if ($height %2 != 0)
				$height ++;

			$this->scaleOptions = ' -vf "scale=' . $width . ':' . $height . '" ';
		}

		if ($this->mediaProperties['audio_codec'] != '')
			$this->options   .= ' -acodec ac3' . $this->audioQuality;

		$this->destinationFile = $destination . '.mp4';
		return $this;
	}

	public function saveAsMP3(string $destination): static
	{
		$this->options       = ' -acodec libmp3lame' . $this->audioQuality;
		$this->destinationFile     = $destination . '.mp3';
		return $this;
	}

	public function saveAsOggVorbis(string $destination): static
	{
		$this->options       = ' -acodec libvorbis' . $this->audioQuality;
		$this->destinationFile     = $destination . '.ogg';
		return $this;
	}

	/**
	 * @throws FrameworkException
	 * @throws Exception
	 */
	public function convertVideo(): static
	{
		if ($this->mediaProperties['video_codec'] == '')
		{
			throw new FrameworkException('Can not convert ' . $this->mediaProperties['filename'] . '. File has no readable video stream');
		}
		return $this->prepareConvertingFile();
	}

	/**
	 * @throws FrameworkException
	 * @throws Exception
	 */
	public function convertAudio(): static
	{
		if ($this->mediaProperties['audio_codec'] == '')
		{
			throw new FrameworkException('Can not convert ' . $this->mediaProperties['filename'] . '. File has no readable audio stream');
		}
		return $this->prepareConvertingFile();
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

		$this->callBinary($command);

		$vidcapPath = $destination . '/vid_1.jpg';
		if (!$this->filesystem->fileExists($vidcapPath))
			throw new FrameworkException('Vid cap ' . $vidcapPath . ' not found');

		return $vidcapPath;
	}

	public function scaleHeight(int $height): static
	{
		$width       = round($this->mediaProperties['width'] / ($this->mediaProperties['height'] / $height));
		if ($width%2 != 0)
			$width++;

		if ($height%2 != 0)
			$height++;

		$this->scaleOptions = ' -vf "scale='.$width.':'.$height.'" ';
		return $this;
	}

	public function scaleWidth(int $width): static
	{
		$this->scaleOptions = ' -vf "scale='.$width.':-1" ';
		return $this;
	}

	public function setAudioQuality(int $frequency, int $bitrate): static
	{
		$this->audioQuality = ' -ar ' . $frequency . ' -ab ' . $bitrate . 'k';
		return $this;
	}

	/**
	 * @throws CoreException
	 */
	protected function setUpBinaryPathsByConfig(): static
	{
		$this->executablePath   = $this->config->getConfigValue('path_to_ffmpeg_bin', 'video');
		$this->probePath       = $this->config->getConfigValue('path_to_ffmpeg_probe', 'video');
		return $this;
	}

	/**
	 * @throws CoreException
	 */
	protected function setUpAudioQualityByConfig(): static
	{
		$this->audioQuality = $this->config->getConfigValue('audio_quality_ffmepg_param', 'video');
		return $this;
	}

	/**
	 * @throws  FrameworkException
	 */
	protected function probeFile(string $filePath): void
	{
		$command    = $this->probePath . ' -v quiet -print_format json -show_format -show_streams ' . $filePath;
		$result     = shell_exec($command);
		$metadata   = json_decode($result);

		if (is_null($metadata) || !isset($metadata->format))
			throw new FrameworkException('Probing media file failed. Unsupported file type for file ' . $filePath . '. Using command: ' . $command);

		$this->parseMediaProperties($metadata);
	}

	protected function parseMediaProperties(stdClass $meta_data): void
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

		$this->mediaProperties['start_time'] =  (string) $meta_data->format->start_time;
		$this->mediaProperties['duration']   =  (string) $meta_data->format->duration;
		$this->mediaProperties['filename']   =  (string) $meta_data->format->filename;
		$this->mediaProperties['filesize']   =  (int) $meta_data->format->size;
		$this->mediaProperties['container']  =  (string) $meta_data->format->format_name;
	}

	/**
	 * @throws FrameworkException
	 * @throws CoreException
	 */
	protected function prepareConvertingFile(): static
	{
		$ffmpeg_thread_param = $this->config->getConfigValue('thread_usage_ffmpeg_param', 'video');

		$command =
			$this->executablePath . ' -i ' .
			$this->mediaProperties['filename'] .
			$this->options .
			$this->scaleOptions .
			$ffmpeg_thread_param .
			' -y ' . $this->destinationFile .
			' 2>&1';

		return $this->callBinary($command);
	}

	/**
	 * @throws  FrameworkException
	 * @throws  Exception
	 */
	protected function callBinary(string $command): static
	{
		$output         = array();
		$return_value   = 0;
		$last_line = exec($command, $output, $return_value);

		if ($return_value != 0)
			throw new FrameworkException('ffmpeg error: Exit code: ' . $return_value . '. Calling: ' . $command, $return_value. 'Last line: ' . $last_line);

		return $this;
	}

	private function setVideoCodec(string $convert_codec): string
	{
		return ' -vcodec '.$convert_codec;
	}

}