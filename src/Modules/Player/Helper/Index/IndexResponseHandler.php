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


namespace App\Modules\Player\Helper\Index;

use App\Modules\Player\Services\PlayerIndexService;
use Psr\Http\Message\ResponseInterface;

class IndexResponseHandler
{
	private readonly FileUtils $fileUtils;
	 private int $fileMTime;
	 private string $etag;
	 private string $lastModified;
	 private string $cacheControl;
	 private string $filePath;
	 private ?string $clientHasNoneMatch;
	 private ?string $clientHasModifiedSince;

	/**
	 * @param FileUtils $fileUtils
	 */
	public function __construct(FileUtils $fileUtils)
	{
		$this->fileUtils = $fileUtils;
	}

	public function init(array $server, string $filePath): void
	{
		$this->filePath               = $filePath;
		$this->clientHasNoneMatch     = $server['HTTP_IF_NONE_MATCH'] ?? $server['If-None-Match'] ?? null;
		$this->clientHasModifiedSince = $server['HTTP_IF_MODIFIED_SINCE'] ?? $server['If-Modified-Since'] ?? null;

		$this->fileMTime    = $this->fileUtils->getFileMTime($filePath);
		$this->etag         = $this->fileUtils->getETag($filePath);
		$this->lastModified = gmdate('D, d M Y H:i:s', $this->fileMTime) . ' GMT';
		$this->cacheControl = 'public, must-revalidate, max-age=864000, pre-check=864000';
	}

	/**
	 * if player sends an If-None-Match with etag then check it and response with
	 * 200 and a new etag
	 *
	 * If etag matches send 304 (not modified)
	 */
	public function doHEAD(ResponseInterface $response): ResponseInterface
	{
		if ($this->clientHasNoneMatch !== null && $this->clientHasNoneMatch === $this->etag)
		{
			return $this->return304($response);
		}

		return $response
			->withHeader('Cache-Control', $this->cacheControl)
			->withHeader('Content-Type', 'application/smil+xml')
			->withHeader('etag', $this->etag)
			->withHeader('Last-Modified', $this->lastModified)
			->withStatus(200);
	}

	/**
	 * If a player do not send any HEAD requests we need to secure that
	 * he will get an index for sure.
	 *
	 * A full index send will happen when:
	 *  1. there is no If-Modified-Since or one with older datetime
	 *  2. there is no If-None-Match sent or is different from current etag
	 *
	 * /+
	 * otherwise 304 status will be sent
	 */
	public function doGET(ResponseInterface $response)
	{
		if ($this->clientHasModifiedSince === null || $this->clientHasNoneMatch === null || (strtotime($this->clientHasModifiedSince) > $this->fileMTime) || $this->clientHasNoneMatch !== $this->etag)
		{
			// not cached or cache outdated, 200 OK send index.smil
			$fileStream = $this->fileUtils->createStream($this->filePath);
			return $response
				->withBody($fileStream)
				->withHeader('Cache-Control', $this->cacheControl)
				->withHeader('etag', $this->etag)
				->withHeader('Last-Modified', $this->lastModified)
				->withHeader('Content-Length', (string) $this->fileUtils->getFileSize($this->filePath)) // will not work with php-fpm or nginx
				->withHeader('Content-Type', 'application/smil+xml')
				->withHeader('Content-Description', 'File Transfer')
				->withHeader('Content-Disposition', 'attachment; filename="' . basename($this->filePath) . '"')
				->withStatus(200);
		}
		else
			return $this->return304($response);
	}


	private function return304(ResponseInterface $response): ResponseInterface
	{
		return $response
			->withHeader('Cache-Control', $this->cacheControl)
			->withHeader('Last-Modified', $this->lastModified)
			->withStatus(304);
	}
}