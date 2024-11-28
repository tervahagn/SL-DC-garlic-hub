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
use App\Framework\Core\Config\IniConfigLoader;
use App\Framework\Core\Locales\Locales;
use App\Framework\Core\Locales\UrlLocaleExtractor;
use App\Framework\Core\Translate\IniTranslationLoader;
use App\Framework\Core\Translate\MessageFormatterFactory;
use App\Framework\Core\Translate\Translator;
use Doctrine\DBAL\DriverManager;
use App\Framework\TemplateEngine\AdapterInterface;
use App\Framework\TemplateEngine\MustacheAdapter;
use App\Framework\TemplateEngine\TemplateService;
use App\Modules\Auth\Repositories\UserMain;
use Phpfastcache\Helper\Psr16Adapter;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;

$dependencies = [];

$dependencies[App::class]             = Di\factory([AppFactory::class, 'createFromContainer']);
$dependencies[Mustache_Engine::class] = DI\factory(function () {
	return new Mustache_Engine(['loader' => new Mustache_Loader_FilesystemLoader(__DIR__ . '/../../templates')]);
});
$dependencies[AdapterInterface::class] = DI\factory(function (Mustache_Engine $mustacheEngine) {
	return new MustacheAdapter($mustacheEngine);
});

$dependencies['SqlConnection'] = DI\factory(function () {
	$connectionParams = [
		'path'     => $_ENV['DB_MASTER_PATH'], // SQLite needs `path`
		'dbname'   => $_ENV['DB_MASTER_NAME'],
		'user'     => $_ENV['DB_MASTER_USER'],
		'password' => $_ENV['DB_MASTER_PASSWORD'],
		'host'     => $_ENV['DB_MASTER_HOST'],
		'port'     => $_ENV['DB_MASTER_PORT'],
		'driver'   => strtolower($_ENV['DB_MASTER_DRIVER']), // e.g. 'pdo_mysql pdo_sqlite '
	];
	return DriverManager::getConnection($connectionParams);
});

$dependencies[Messages::class] = DI\factory(function () {return new Messages();});
$dependencies[UserMain::class] = DI\factory(function (ContainerInterface $container) {
	return new UserMain($container->get('SqlConnection'));
});
$dependencies[Config::class] = DI\factory(function (ContainerInterface $container) {
	return new Config(new IniConfigLoader($container->get('paths')['configDir']));
});
$dependencies[Locales::class] = DI\factory(function (ContainerInterface $container) {
	return new Locales(
		$container->get(Config::class),
		new UrlLocaleExtractor()
	);
});
$dependencies[Translator::class] = DI\factory(function (ContainerInterface $container) {
	return new Translator(
		$container->get(Locales::class),
		new IniTranslationLoader($container->get('paths')['translationsDir']),
		new MessageFormatterFactory(),
		new Psr16Adapter('Files')
	);
});


return $dependencies;