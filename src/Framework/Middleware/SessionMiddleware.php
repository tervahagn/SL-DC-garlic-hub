<?php

namespace App\Framework\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Flash\Messages;
use SlimSession\Helper;

class SessionMiddleware implements MiddlewareInterface
{
	private Helper $session;

	public function __construct(Helper $session)
	{
		$this->session = $session;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$request = $request->withAttribute('session', $this->session);
		$request = $request->withAttribute('flash', new Messages());

		return $handler->handle($request);
	}
}
