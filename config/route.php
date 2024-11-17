<?php

use App\Controller\HomeController;
use App\Modules\Auth\Controller\LoginController;
use App\Modules\Auth\Controller\LoginControllerFactory;

/* @var \Slim\App $app */
$container = $app->getContainer();
$app->get('/', [HomeController::class, 'index']);
$app->get('/login', function ($request, $response) use ($container)
{
	return LoginControllerFactory::create($container)->showLogin($request, $response);
});
$app->post('/login', function ($request, $response) use ($container)
{
	return LoginControllerFactory::create($container)->login($request, $response);
});
$app->get('/logout', function ($request, $response) use ($container)
{
	return LoginControllerFactory::create($container)->logout($request, $response);
});
