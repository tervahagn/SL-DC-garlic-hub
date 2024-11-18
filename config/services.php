<?php

use App\Framework\Core\Config\Config;
use App\Framework\Database\Adapters\Factory;
use App\Framework\Database\DBHandler;
use App\Framework\Database\QueryBuilder;
use App\Framework\TemplateEngine\AdapterInterface;
use App\Framework\TemplateEngine\MustacheAdapter;
use App\Framework\TemplateEngine\TemplateService;
use Slim\App;
use Slim\Factory\AppFactory;

$dependencies = [];

$dependencies[App::class]             = Di\factory([AppFactory::class, 'createFromContainer']);
$dependencies[Config::class]          = DI\factory(function () {
	return new Config(new \App\Framework\Core\Config\IniConfigLoader(__DIR__ . '/../config/'));
});
$dependencies[Mustache_Engine::class] = DI\factory(function () {
	return new Mustache_Engine(['loader' => new Mustache_Loader_FilesystemLoader(__DIR__ . '/../templates')]);
});
$dependencies[AdapterInterface::class] = DI\factory(function (Mustache_Engine $mustacheEngine) {
	return new MustacheAdapter($mustacheEngine);
});
$dependencies[TemplateService::class]  = DI\factory(function (AdapterInterface $adapter) {
	return new TemplateService($adapter);
});
$dependencies[QueryBuilder::class]     = DI\factory(function () {return new QueryBuilder();});
$dependencies[DBHandler::class]        = DI\factory(function () {
	$credentials = [
		'db_path'   => $_ENV['DB_MASTER_PATH'],
		'db_driver' => $_ENV['DB_MASTER_DRIVER'],
		'db_user'   => $_ENV['DB_MASTER_USER'],
		'db_pass'   => $_ENV['DB_MASTER_PASSWORD'],
		'db_host'   => $_ENV['DB_MASTER_HOST'],
		'db_port'   => $_ENV['DB_MASTER_PORT'],
		'db_name'   => $_ENV['DB_MASTER_NAME']
	];
	return Factory::createConnection($credentials);
});

$dependencies[\Slim\Flash\Messages::class] = DI\factory(function () {return new \Slim\Flash\Messages();});

return $dependencies;