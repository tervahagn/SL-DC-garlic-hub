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


use DI\DependencyException;
use DI\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;


set_error_handler(/**
 * @throws ErrorException
 */ function ($errNumber, $errorString, $errorFile, $errorLine)
{
	if (!(error_reporting() & $errNumber)) 	// ignore errors when suppressed via @
		return false;

	throw new \ErrorException($errorString, 0, $errNumber, $errorFile, $errorLine);
});

/*
 * maybe useful for later
register_shutdown_function(function () use ($container)
{
	$error = error_get_last();
	if (is_null($error))
		return;
});
*/

	/** @var ContainerInterface $container */
	$app    = $container->get(App::class);
	$logger = $container->get('AppLogger');

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	$myErrorHandler = function(ServerRequestInterface $request,	\Throwable $exception) use ($app, $logger)
	{
		$logger->error($exception->getMessage());
		$route = $app->getRouteCollector()->getRouteParser()->current();
		if (str_contains($route, 'async'))
			$payload = ['success' => false, 'error_message' => $exception->getMessage()];
		else
			$payload = ['error_message' => $exception->getMessage()];

		$response = $app->getResponseFactory()->createResponse();
		return $response->getBody()->write(json_encode($payload));
	};

	$errorMiddleware = $app->addErrorMiddleware($_ENV['APP_DEBUG'], true, true, $container->get('AppLogger'));
	$errorMiddleware->setDefaultErrorHandler($myErrorHandler);
