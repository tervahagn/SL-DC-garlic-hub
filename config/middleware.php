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
use App\Framework\Core\Cookie;
use App\Framework\Core\Locales\Locales;
use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Middleware\AuthMiddleware;
use App\Framework\Middleware\EnvironmentMiddleware;
use App\Framework\Middleware\SessionMiddleware;
use App\Modules\Auth\AuthService;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Flash\Messages;

return function (ContainerInterface $container, $start_time, $start_memory): App
{
	/** @var App $app */
	$app = $container->get(App::class);

	require_once __DIR__ . '/route.php';

	// Environment Middleware
	$app->add(new EnvironmentMiddleware(
		$container->get(Config::class),
		$container->get(Locales::class),
		$container->get(Translator::class)
	));

	$app->add(new AuthMiddleware($container->get(AuthService::class)));
	$app->add(new SessionMiddleware(
		$container->get(Session::class),
		$container->get(Messages::class),
		$container->get(Cookie::class)));

	// Timing Middleware
	$app->add(function ($request, $handler) use ($start_time, $start_memory) {
		$request = $request->withAttribute('start_time', $start_time);
		$request = $request->withAttribute('start_memory', $start_memory);
		return $handler->handle($request);
	});

	$app->addRoutingMiddleware();

	require __DIR__ . '/error_handling.php'; // call error middleware as last

	return $app;
};
