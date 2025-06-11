<?php

namespace Tests\Unit\Framework\Media;

use App\Framework\Media\MediaProperties;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class MediaPropertiesTest extends TestCase
{
	private MediaProperties $mediaProperties;

	protected function setUp(): void
	{
		$this->mediaProperties = new MediaProperties();
	}

	#[Group('units')]
	public function testMediaProperties(): void
	{
		$json = '{"format": {"filename": "test.mp4", "size": 12345, "format_name": "mp4", "duration": 60, "start_time": 0}, "streams": [{"codec_type": "video", "codec_name": "h264", "width": 1920, "height": 1080}]}';

		$stdClass = json_decode($json);
		$this->mediaProperties->fromStdClass($stdClass);

		$this->assertEquals('test.mp4', $this->mediaProperties->getFilename());
		$this->assertEquals(60, $this->mediaProperties->getDuration());
	}

	#[Group('units')]
	public function testMediaPropertiesWithUserValues(): void
	{
		$json = '{"format": {"filename": "test.mp4", "size": 12345, "format_name": "mp4", "start_time": 0}, "streams": [{"codec_type": "video", "codec_name": "h264", "width": 1920, "height": 1080}]}';

		$stdClass = json_decode($json);
		$this->mediaProperties->fromStdClass($stdClass, ['duration' => '160']);

		$this->assertEquals('test.mp4', $this->mediaProperties->getFilename());
		$this->assertEquals(160, $this->mediaProperties->getDuration());
	}

	#[Group('units')]
	public function testFromArrayWithVideoAndAudioStream(): void
	{
		$json = '{"format": {"filename": "media.mp4", "size": 10000, "format_name": "mp4", "duration": 120, "start_time": 0}, "streams": [{"codec_type": "video", "codec_name": "vp9", "width": 1280, "height": 720}, {"codec_type": "audio", "codec_name": "aac"}]}';

		$stdClass = json_decode($json);
		$this->mediaProperties->fromStdClass($stdClass);

		$this->assertEquals(1280, $this->mediaProperties->getWidth());
		$this->assertEquals(720, $this->mediaProperties->getHeight());
		$this->assertEquals('vp9', $this->mediaProperties->getVideoCodec());
		$this->assertEquals('aac', $this->mediaProperties->getAudioCodec());
		$this->assertEquals('media.mp4', $this->mediaProperties->getFilename());
		$this->assertTrue($this->mediaProperties->hasVideoStream());

	}

	#[Group('units')]
	public function testToArray(): void
	{
		// Test default values in toArray output
		$expected = [
			'width' => 0,
			'height' => 0,
			'video_codec' => '',
			'audio_codec' => '',
			'aspect_ratio' => '',
			'start_time' => '',
			'duration' => 0.0,
			'filename' => '',
			'filesize' => 0,
			'container' => '',
		];
		$this->assertFalse($this->mediaProperties->hasVideoStream());

		$this->assertEquals($expected, $this->mediaProperties->toArray());
	}

	#[Group('units')]
	public function testToArrayWithPopulatedValues(): void
	{
		// Set the object values using fromStdClass
		$json = '{"format": {"filename": "video.mp4", "size": 20000, "format_name": "mkv", "duration": 150, "start_time": 0.5}, "streams": [{"codec_type": "video", "codec_name": "h265", "width": 1280, "height": 720}, {"codec_type": "audio", "codec_name": "aac"}]}';
		$stdClass = json_decode($json);
		$this->mediaProperties->fromStdClass($stdClass);

		// Test populated values in toArray output
		$expected = [
			'width' => 1280,
			'height' => 720,
			'video_codec' => 'h265',
			'audio_codec' => 'aac',
			'aspect_ratio' => '1:1', // Default aspect ratio in the absence of data
			'start_time' => '0.5',
			'duration' => 150.0,
			'filename' => 'video.mp4',
			'filesize' => 20000,
			'container' => 'mkv',
		];

		$this->assertEquals($expected, $this->mediaProperties->toArray());
	}

	#[Group('units')]
	public function testReset(): void
	{
		// Populate the MediaProperties object with sample values
		$json = '{"format": {"filename": "sample.mp4", "size": 50000, "format_name": "avi", "duration": 300.5, "start_time": 0.1}, "streams": [{"codec_type": "video", "codec_name": "mpeg4", "width": 1024, "height": 768}, {"codec_type": "audio", "codec_name": "mp3"}]}';
		$stdClass = json_decode($json);
		$this->mediaProperties->fromStdClass($stdClass);

		// Reset the object
		$this->mediaProperties->reset();

		// Verify that all properties have been reset to defaults
		$this->assertEquals(0, $this->mediaProperties->getWidth());
		$this->assertEquals(0, $this->mediaProperties->getHeight());
		$this->assertEquals('', $this->mediaProperties->getVideoCodec());
		$this->assertEquals('', $this->mediaProperties->getAudioCodec());
		$this->assertEquals('', $this->mediaProperties->getAspectRatio());
		$this->assertEquals('', $this->mediaProperties->getStartTime());
		$this->assertEquals(0.0, $this->mediaProperties->getDuration());
		$this->assertEquals('', $this->mediaProperties->getFilename());
		$this->assertEquals(0, $this->mediaProperties->getFilesize());
		$this->assertEquals('', $this->mediaProperties->getContainer());
	}
}
