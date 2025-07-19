<?php
declare(strict_types=1);

namespace App\Modules\Player\Helper\PlayerPlaylist;

use App\Framework\Controller\BaseResponseBuilder;
use Psr\Http\Message\ResponseInterface;

class ResponseBuilder extends BaseResponseBuilder
{
    public function invalidPlayerId(ResponseInterface $response): ResponseInterface
    {
		return $this->jsonResponseHandler->jsonError(
			$response, $this->translator->translate('invalid_player_id', 'player')
		);
    }

    public function invalidPlaylistId(ResponseInterface $response): ResponseInterface
    {
		return $this->jsonResponseHandler->jsonError(
			$response, $this->translator->translate('invalid_playlist_id', 'playlist')
		);
    }

    public function invalidItemId(ResponseInterface $response): ResponseInterface
    {
		return $this->jsonResponseHandler->jsonError(
			$response, $this->translator->translate('invalid_item_id', 'playlist')
		);
    }

    public function playerNotFound(ResponseInterface $response): ResponseInterface
    {
		return $this->jsonResponseHandler->jsonError(
			$response, $this->translator->translate('player_not_found', 'player')
		);
    }

    public function playerNotReachable(ResponseInterface $response): ResponseInterface
    {
		return $this->jsonResponseHandler->jsonError(
			$response, $this->translator->translate('player_not_reachable', 'player')
		);
    }

    public function noPlaylistAssigned(ResponseInterface $response): ResponseInterface
    {
		return $this->jsonResponseHandler->jsonError(
			$response, $this->translator->translate('no_playlist_assigned', 'player')
		);
    }
}
