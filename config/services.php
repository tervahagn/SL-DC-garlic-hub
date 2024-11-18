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