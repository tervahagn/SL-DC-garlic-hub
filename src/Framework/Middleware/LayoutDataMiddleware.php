<?php

namespace App\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * The LayoutDataMiddleware class adds common layout data
 * (such as metadata, menu items, and legal information) to the request,
 * making it available for use in templates.
 * It then passes the request to the next middleware
 * or handler in the pipeline.
 */
class LayoutDataMiddleware implements MiddlewareInterface
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$layoutData = [
			'menu' => [
				['URL' => '/player', 'LANG_MENU_POINT' => 'Player'],
				['URL' => '/playlists', 'LANG_MENU_POINT' => 'Playlists'],
				['URL' => '/mediapool', 'LANG_MENU_POINT' => 'Mediapool']
			],

			'LANG_LEGALS' => 'Web Legals',
			'LANG_PRIVACY' => 'Privacy',
			'LANG_TERMS' => 'Terms'
		];

		// Daten dem Request hinzufügen
		$request = $request->withAttribute('layoutData', $layoutData);

		// Weiter zur nächsten Middleware oder zum Handler
		return $handler->handle($request);
	}
}
