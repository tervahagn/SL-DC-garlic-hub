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

use App\Commands\MigrateCommand;
use App\Framework\Core\Config\Config;
use App\Framework\Core\Locales\Locales;
use App\Framework\Core\Locales\SessionLocaleExtractor;
use App\Framework\Core\Locales\UrlLocaleExtractor;
use App\Framework\Core\Translate\IniTranslationLoader;
use App\Framework\Core\Translate\MessageFormatterFactory;
use App\Framework\Core\Translate\Translator;
use App\Framework\Migration\Repository;
use App\Framework\Migration\Runner;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use App\Framework\TemplateEngine\AdapterInterface;
use App\Framework\TemplateEngine\MustacheAdapter;
use App\Modules\Auth\Repositories\UserMain;
use Doctrine\DBAL\Logging\Middleware;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Phpfastcache\Helper\Psr16Adapter;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Middleware\Session;
use SlimSession\Helper;
use Symfony\Component\Console\Application;

$dependencies = [];
$dependencies[LoggerInterface::class] = DI\factory(function (ContainerInterface $container) {
	$logger = new Logger('dbal');
	$config = $container->get(Config::class);
	$logger->pushHandler(new StreamHandler($config->getPaths('logDir').'/app.log', $config->getLogLevel()));

	return $logger;
});
$dependencies[App::class]         = Di\factory([AppFactory::class, 'createFromContainer']); // Slim App
$dependencies[Application::class] = DI\factory(function (ContainerInterface $container) { // symfony console application
	return new Application();
});
$dependencies[Session::class] = DI\factory(function () {
	return new Session([
		'name' => 'garlic_session',
		'autorefresh' => true,
		'lifetime' => '1 hour',
	]);
});
$dependencies[Helper::class] = DI\factory(function () {
	return new Helper();
});
$dependencies[Locales::class] = DI\factory(function (ContainerInterface $container) {
	return new Locales(
		$container->get(Config::class),
		new SessionLocaleExtractor($container->get(Helper::class))
	);
});
$dependencies[Translator::class] = DI\factory(function (ContainerInterface $container) {
	$translationDir = $container->get(Config::class)->getPaths('translationDir');
	return new Translator(
		$container->get(Locales::class),
		new IniTranslationLoader($translationDir),
		new MessageFormatterFactory(),
		new Psr16Adapter('Files')
	);
});
$dependencies[Mustache_Engine::class] = DI\factory(function () {
	return new Mustache_Engine(['loader' => new Mustache_Loader_FilesystemLoader(__DIR__ . '/../../templates')]);
});
$dependencies[AdapterInterface::class] = DI\factory(function (Mustache_Engine $mustacheEngine) {
	return new MustacheAdapter($mustacheEngine);
});
$dependencies['SqlConnection'] = DI\factory(function (ContainerInterface $container) {
	$config = $container->get(Config::class);
	$connectionParams = [
		'path'     => $config->getEnv('DB_MASTER_PATH'), // SQLite needs `path`
		'dbname'   => $config->getEnv('DB_MASTER_NAME'),
		'user'     => $config->getEnv('DB_MASTER_USER'),
		'password' => $config->getEnv('DB_MASTER_PASSWORD'),
		'host'     => $config->getEnv('DB_MASTER_HOST'),
		'port'     => $config->getEnv('DB_MASTER_PORT'),
		'driver'   => strtolower($config->getEnv('DB_MASTER_DRIVER')), // e.g. 'pdo_mysql pdo_sqlite '
	];

	$logger = new Logger('dbal');
	$logger->pushHandler(new StreamHandler($config->getPaths('logDir').'/dbal.log', $config->getLogLevel()));
	$dbalConfig = new Configuration();
	$dbalConfig->setMiddlewares([new Middleware($logger)]);

	return DriverManager::getConnection($connectionParams, $dbalConfig);
});
$dependencies['LocalFileSystem'] = DI\factory(function (ContainerInterface $container) {
	$systemDir = $container->get(Config::class)->getPaths('systemDir');
	return new Filesystem(new LocalFilesystemAdapter($systemDir));
});
if (php_sapi_name() === 'cli')
{
	$dependencies[Repository::class] = DI\factory(function (ContainerInterface $container) {
		return new Repository($container->get('SqlConnection'));
	});
	$dependencies[Runner::class] = DI\factory(function (ContainerInterface $container) {

		$config = $container->get(Config::class);
		$path   = $config->getPaths('migrationDir').'/'.$config->getEnv('APP_PLATFORM_EDITION').'/';
		return new Runner(
			$container->get(Repository::class),
			new Filesystem(new LocalFilesystemAdapter($path))
		);
	});
	$dependencies[MigrateCommand::class] = DI\factory(function (ContainerInterface $container) {
		return new MigrateCommand($container->get(Runner::class));
	});
}
$dependencies[Messages::class] = DI\factory(function () {return new Messages();});
$dependencies[UserMain::class] = DI\factory(function (ContainerInterface $container) {
	return new UserMain($container->get('SqlConnection'));
});


return $dependencies;