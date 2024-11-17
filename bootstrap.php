<?php
$start_time   = microtime(true);
$start_memory = memory_get_usage();

use App\Framework\Middleware\FinalRenderMiddleware;
use App\Framework\Middleware\LayoutDataMiddleware;
use App\Framework\Middleware\SessionMiddleware;
use App\Framework\TemplateEngine\MustacheAdapter;
use DI\ContainerBuilder;
use Slim\App;
use Slim\Middleware\Session;

/* @var App $app */
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$ContainerBuilder = new ContainerBuilder();
$ContainerBuilder->addDefinitions(__DIR__ . '/config/services.php');

try
{
	$DiContainer     = $ContainerBuilder->build();
	$app             = $DiContainer->get(App::Class);

	$mustache = new Mustache_Engine([
		'loader' => new Mustache_Loader_FilesystemLoader(__DIR__ . '/templates'),
	]);
	require_once __DIR__.'/config/route.php';

	$errorMiddleware = $app->addErrorMiddleware($_ENV['APP_DEBUG'], true, true);

	$app->add(new FinalRenderMiddleware(new MustacheAdapter($mustache)));
	$app->add(new LayoutDataMiddleware());

	$app->add(new SessionMiddleware(new \SlimSession\Helper()));
	$app->add(new Session(['name' => 'garlic_session','autorefresh' => true, 'lifetime' => '1 hour']));

	$app->add(function ($request, $handler) use	($start_time, $start_memory)
	{
		$request = $request->withAttribute('start_time', $start_time);
		$request = $request->withAttribute('start_memory', $start_memory);
		return $handler->handle($request);
	});

	$app->addRoutingMiddleware();
}
catch (Exception $e)
{
	echo 'Exception: ' . $e->getMessage();
	exit();
}
return $app;