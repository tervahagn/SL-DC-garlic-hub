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
use App\Framework\Middleware\EnvironmentMiddleware;
use App\Framework\Middleware\FinalRenderMiddleware;
use App\Framework\Middleware\LayoutDataMiddleware;
use App\Framework\Middleware\SessionMiddleware;
use App\Framework\TemplateEngine\MustacheAdapter;
use Phpfastcache\Helper\Psr16Adapter;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Middleware\Session;
use SlimSession\Helper;

return function (ContainerInterface $container, $start_time, $start_memory): App {
	// App-Instanz aus dem Container abrufen
	$app = $container->get(App::class);

	// Error Middleware
	$errorMiddleware = $app->addErrorMiddleware($_ENV['APP_DEBUG'], true, true);
	$errorMiddleware->setDefaultErrorHandler(function (
		\Psr\Http\Message\ServerRequestInterface $request,
		\Throwable $exception,
		bool $displayErrorDetails
	) use ($container)
	{
		$logger = $container->get(LoggerInterface::class);
		$logger->error('Unhandled exception', [
			'message' => $exception->getMessage(),
			'trace' => $exception->getTraceAsString(),
		]);
		if ($displayErrorDetails) // only when $_ENV['APP_DEBUG'] is true
			throw $exception;
	});
	// Final Render Middleware (AFTER Controllers)
	$app->add(new FinalRenderMiddleware(
		new MustacheAdapter($container->get(Mustache_Engine::class))
	));

	// Routes laden
	require_once __DIR__ . '/route.php';

	// Layout Data Middleware (BEFORE Controllers)
	$app->add(new LayoutDataMiddleware());

	// Environment Middleware
	$app->add(new EnvironmentMiddleware(
		$container->get(Config::class),
		$container->get(Locales::class),
		$container->get(Translator::class)
	));

	// Session Middleware
	$app->add(new SessionMiddleware(new Helper()));
	$app->add(new Session([
		'name' => 'garlic_session',
		'autorefresh' => true,
		'lifetime' => '1 hour',
	]));

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
