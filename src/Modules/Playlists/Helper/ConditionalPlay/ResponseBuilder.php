<?php
declare(strict_types=1);

namespace App\Modules\Playlists\Helper\ConditionalPlay;

use App\Framework\Controller\BaseResponseBuilder;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;

class ResponseBuilder extends BaseResponseBuilder
{
	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function invalidItemId(ResponseInterface $response): ResponseInterface
    {
		return $this->jsonResponseHandler->jsonError(
			$response, $this->translator->translate('invalid_item_id', 'playlists')
		);
    }

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function playlistNotFound(ResponseInterface $response): ResponseInterface
    {
		return $this->jsonResponseHandler->jsonError(
			$response, $this->translator->translate('playlist_not_found', 'playlists')
		);
    }

	public function itemNotFound(ResponseInterface $response)
	{
		return $this->jsonResponseHandler->jsonError(
			$response, $this->translator->translate('item_not_found', 'playlists')
		);
	}

}
