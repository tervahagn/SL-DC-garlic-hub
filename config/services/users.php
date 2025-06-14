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


use App\Framework\Core\Acl\AclHelper;
use App\Framework\Core\Config\Config;
use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Utils\Datatable\BuildService;
use App\Framework\Utils\Datatable\DatatableTemplatePreparer;
use App\Framework\Utils\Datatable\PrepareService;
use App\Framework\Utils\Forms\FormTemplatePreparer;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Users\Controller\EditLocalesController;
use App\Modules\Users\Controller\EditPasswordController;
use App\Modules\Users\Controller\ShowDatatableController;
use App\Modules\Users\Controller\ShowEditUserController;
use App\Modules\Users\Controller\UsersController;
use App\Modules\Users\Entities\UserEntityFactory;
use App\Modules\Users\Helper\Datatable\ControllerFacade;
use App\Modules\Users\Helper\Datatable\DatatableBuilder;
use App\Modules\Users\Helper\Datatable\DatatablePreparer;
use App\Modules\Users\Helper\Datatable\Parameters;
use App\Modules\Users\Helper\Settings\Facade;
use App\Modules\Users\Repositories\Edge\UserMainRepository;
use App\Modules\Users\Repositories\UserRepositoryFactory;
use App\Modules\Users\Services\AclValidator;
use App\Modules\Users\Services\UsersDatatableService;
use App\Modules\Users\Services\UsersService;
use Phpfastcache\Helper\Psr16Adapter;
use Psr\Container\ContainerInterface;

$dependencies = [];
$dependencies[AclValidator::class] = DI\factory(function (ContainerInterface $container)
{
	return new AclValidator($container->get(AclHelper::class));
});
$dependencies[UsersService::class] = DI\factory(function (ContainerInterface $container)
{
	return new UsersService(
		new UserRepositoryFactory($container->get(Config::class), $container->get('SqlConnection')),
		new UserEntityFactory($container->get(Config::class)),
		$container->get(Psr16Adapter::class),
		$container->get('ModuleLogger')
	);
});
$dependencies[UsersDatatableService::class] = DI\factory(function (ContainerInterface $container)
{
	return new UsersDatatableService(
		new UserMainRepository($container->get('SqlConnection')),
		$container->get(Parameters::class),
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
	return new UsersController($container->get(UsersDatatableService::class), $container->get(Parameters::class));
});
$dependencies[ShowDatatableController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowDatatableController(
		$container->get(ControllerFacade::class),
		$container->get(DatatableTemplatePreparer::class)
	);
});

$dependencies[DatatableBuilder::class] = DI\factory(function (ContainerInterface $container)
{
	return new DatatableBuilder(
		$container->get(BuildService::class),
		$container->get(Parameters::class),
		$container->get(AclValidator::class)
	);
});
$dependencies[DatatablePreparer::class] = DI\factory(function (ContainerInterface $container)
{
	return new DatatablePreparer(
		$container->get(PrepareService::class),
		$container->get(AclValidator::class),
		$container->get(Parameters::class)
	);
});
$dependencies[ControllerFacade::class] = DI\factory(function (ContainerInterface $container)
{
	return new ControllerFacade(
		$container->get(DatatableBuilder::class),
		$container->get(DatatablePreparer::class),
		$container->get(UsersDatatableService::class)
	);
});

$dependencies[ShowDatatableController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowDatatableController(
		$container->get(ControllerFacade::class),
		$container->get(DatatableTemplatePreparer::class)
	);
});

$dependencies[ShowEditUserController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowEditUserController(
		$container->get(Facade::class),
		$container->get(FormTemplatePreparer::class)
	);
});


return $dependencies;