<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

use App\Framework\Core\Config\Config;
use App\Framework\Core\Locales\Locales;
use App\Framework\Core\Translate\Translator;
use App\Framework\Middleware\AuthMiddleware;
use App\Framework\Middleware\EnvironmentMiddleware;
use App\Framework\Middleware\FinalRenderMiddleware;
use App\Framework\Middleware\LayoutDataMiddleware;
use App\Framework\Middleware\SessionMiddleware;
use App\Framework\TemplateEngine\MustacheAdapter;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Middleware\Session;
use Slim\Psr7\Response;
use SlimSession\Helper;

return function (ContainerInterface $container, $start_time, $start_memory): App
{

	/** @var App $app */
	$app = $container->get(App::class);

	set_error_handler(function ($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) // ignore errors when suppressed via @
			return false;

		throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
	});

	register_shutdown_function(function () use ($container) {
		$error = error_get_last();
		if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR]))
		{
			$logger = $container->get('AppLogger');
			$logger->error('Fatal Error', $error);

			http_response_code(200);
			echo json_encode([
				'success' => false,
				'error_message' => 'A critical error occurred. Please try again later.'
			]);
		}
	});

	// Error Middleware
	$errorMiddleware = $app->addErrorMiddleware(
		$_ENV['APP_DEBUG'],
		true,
		true,
		//$container->get('AppLogger')
	);
	$errorMiddleware->setDefaultErrorHandler(function (
		ServerRequestInterface $request,
		\Throwable $exception,
		bool $displayErrorDetails
	) use ($container)
	{
		$logger = $container->get('AppLogger');
		$logger->error('Unhandled exception', [
			'message' => $exception->getMessage(),
			'trace' => $exception->getTraceAsString(),
		]);

		$response = new Response();
		$response->getBody()->write(json_encode(['success' => false, 'error_message' => $exception->getMessage()]));
		return $response->withStatus(200);
	});

	// Final Render Middleware (AFTER UI-Controllers)
	if (!str_contains($_SERVER['REQUEST_URI'], 'async') && !str_contains($_SERVER['REQUEST_URI'], 'api'))
		$app->add($container->get(FinalRenderMiddleware::class));
	else
		$app->add(function ($request, $handler)	{return $handler->handle($request)->withHeader('Content-Type', 'text/html');});

	require_once __DIR__ . '/route.php';

	// Layout Data Middleware (BEFORE UI-Controllers)  SLIM midddleware order is vice versa
	if (!str_contains($_SERVER['REQUEST_URI'], 'async') && !str_contains($_SERVER['REQUEST_URI'], 'api'))
		$app->add(new LayoutDataMiddleware());

	// Environment Middleware
	$app->add(new EnvironmentMiddleware(
		$container->get(Config::class),
		$container->get(Locales::class),
		$container->get(Translator::class)
	));

	$app->add(new AuthMiddleware());

	// Session Middleware
	$app->add(new SessionMiddleware($container->get(Helper::class)));
	$app->add($container->get(Session::class));

	// Timing Middleware
	$app->add(function ($request, $handler) use ($start_time, $start_memory) {
		$request = $request->withAttribute('start_time', $start_time);
		$request = $request->withAttribute('start_memory', $start_memory);
		return $handler->handle($request);
	});

	// Routing Middleware
	$app->addRoutingMiddleware();

	// App zurückgeben
	return $app;
};
