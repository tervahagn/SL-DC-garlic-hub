<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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


namespace App\Framework\Media;

use stdClass;

class MediaProperties
{
	private int $width = 0;
	private int $height = 0;
	private string $videoCodec = '';
	private string $audioCodec = '';
	private string $aspectRatio = '';
	private string $startTime = '';
	private float $duration = 0.0;
	private string $filename = '';
	private int $filesize = 0;
	private string $container = '';

	public function __construct()
	{
	}

	public function getWidth(): int
	{
		return $this->width;
	}

	public function getHeight(): int
	{
		return $this->height;
	}

	public function getVideoCodec(): string
	{
		return $this->videoCodec;
	}

	public function getAudioCodec(): string
	{
		return $this->audioCodec;
	}

	public function getAspectRatio(): string
	{
		return $this->aspectRatio;
	}

	public function getStartTime(): string
	{
		return $this->startTime;
	}

	public function getDuration(): float
	{
		return $this->duration;
	}

	public function getFilename(): string
	{
		return $this->filename;
	}

	public function getFilesize(): int
	{
		return $this->filesize;
	}

	public function getContainer(): string
	{
		return $this->container;
	}

	public function hasVideoStream(): bool
	{
		return $this->videoCodec !== '';
	}

	public function reset(): void
	{
		$this->width = 0;
		$this->height = 0;
		$this->videoCodec = '';
		$this->audioCodec = '';
		$this->aspectRatio = '';
		$this->startTime = '';
		$this->duration = 0.0;
		$this->filename = '';
		$this->filesize = 0;
		$this->container = '';
	}

	public function toArray(): array
	{
		return [
			'width' => $this->width,
			'height' => $this->height,
			'video_codec' => $this->videoCodec,
			'audio_codec' => $this->audioCodec,
			'aspect_ratio' => $this->aspectRatio,
			'start_time' => $this->startTime,
			'duration' => $this->duration,
			'filename' => $this->filename,
			'filesize' => $this->filesize,
			'container' => $this->container,
		];
	}

	public function fromArray(stdClass $metadata, array $userMetadata = []): void
	{
		foreach ($metadata->streams as $stream)
		{
			if ($stream->codec_type === 'video' && isset($stream->codec_name))
			{
				$this->videoCodec  = (string)$stream->codec_name;
				$this->aspectRatio = isset($stream->display_aspect_ratio) ? (string)$stream->display_aspect_ratio : '1:1';
				$this->width  = (int)$stream->width;
				$this->height = (int)$stream->height;
			}
			elseif ($stream->codec_type === 'audio')
			{
				$this->audioCodec = (string)$stream->codec_name;
			}
		}

		// Handle duration from various sources
		$duration = 0;
		if (isset($metadata->format->duration))
			$duration = (float) $metadata->format->duration;
		 elseif (array_key_exists('duration', $userMetadata))
			$duration = (float) $userMetadata['duration'];

		$this->startTime = (string) $metadata->format->start_time;
		$this->duration  = $duration;
		$this->filename  = (string) $metadata->format->filename;
		$this->filesize  = (int) $metadata->format->size;
		$this->container = (string) $metadata->format->format_name;
	}

}