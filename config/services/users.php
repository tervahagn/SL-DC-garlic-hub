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
use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\Datatable\BaseDataGridTemplateFormatter;
use App\Framework\Utils\Datatable\BuildServiceLocator;
use App\Framework\Utils\Datatable\FormatterServiceLocator;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Users\Helper\Datatable\Facade;
use App\Modules\Users\Controller\ShowDatatableController;
use App\Modules\Users\Controller\UsersController;
use App\Modules\Users\EditLocalesController;
use App\Modules\Users\EditPasswordController;
use App\Modules\Users\Entities\UserEntityFactory;
use App\Modules\Users\Helper\Datatable\DatatableBuilder;
use App\Modules\Users\Helper\Datatable\DatatableFormatter;
use App\Modules\Users\Helper\Datatable\Parameters;
use App\Modules\Users\Repositories\Edge\UserMainRepository;
use App\Modules\Users\Repositories\UserRepositoryFactory;
use App\Modules\Users\Services\AclValidator;
use App\Modules\Users\Services\UsersOverviewService;
use App\Modules\Users\Services\UsersService;
use Phpfastcache\Helper\Psr16Adapter;
use Psr\Container\ContainerInterface;

$dependencies = [];
$dependencies[AclValidator::class] = DI\factory(function (ContainerInterface $container)
{
	return new AclValidator(
		'users',
		$container->get(UsersService::class),
		$container->get(Config::class),
	);
});
$dependencies[UsersService::class] = DI\factory(function (ContainerInterface $container)
{
	return new UsersService(
		new UserRepositoryFactory($container->get(Config::class), $container->get('SqlConnection')),
		new UserEntityFactory($container->get(Config::class)),
		$container->get(Psr16Adapter::class)
	);
});
$dependencies[UsersOverviewService::class] = DI\factory(function (ContainerInterface $container)
{
	return new UsersOverviewService(
		new UserMainRepository($container->get('SqlConnection')),
		$container->get(AclValidator::class),
		$container->get('ModuleLogger')
	);
});
$dependencies[EditPasswordController::class] = DI\factory(function (ContainerInterface $container)
{
	return new EditPasswordController(
		$container->get(FormBuilder::class),
		$container->get(UsersService::class)
	);
});
$dependencies[EditLocalesController::class] = DI\factory(function (ContainerInterface $container)
{
	return new EditLocalesController($container->get(UsersService::class));
});
$dependencies[Parameters::class] = DI\factory(function (ContainerInterface $container)
{
	return new Parameters(
		$container->get(Sanitizer::class),
		$container->get(Session::class)
	);
});
$dependencies[UsersController::class] = DI\factory(function (ContainerInterface $container)
{
	return new UsersController($container->get(UsersOverviewService::class), $container->get(Parameters::class));
});
$dependencies[ShowDatatableController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowDatatableController(
		$container->get(\App\Modules\Users\Helper\Datatable\Facade::class),
		$container->get(BaseDataGridTemplateFormatter::class)
	);
});

$dependencies[DatatableBuilder::class] = DI\factory(function (ContainerInterface $container)
{
	return new DatatableBuilder(
		$container->get(BuildServiceLocator::class),
		$container->get(Parameters::class),
		$container->get(Translator::class),
		$container->get(Config::class)
	);
});
$dependencies[DatatableFormatter::class] = DI\factory(function (ContainerInterface $container)
{
	return new DatatableFormatter(
		$container->get(FormatterServiceLocator::class),
		$container->get(Translator::class),
		$container->get(AclValidator::class),
		$container->get(Config::class)
	);
});
$dependencies[Facade::class] = DI\factory(function (ContainerInterface $container)
{
	return new Facade(
		$container->get(DatatableBuilder::class),
		$container->get(DatatableFormatter::class),
		$container->get(Parameters::class),
		$container->get(UsersOverviewService::class)
	);
});

$dependencies[ShowDatatableController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowDatatableController(
		$container->get(Facade::class),
		$container->get(BaseDataGridTemplateFormatter::class)
	);
});


return $dependencies;