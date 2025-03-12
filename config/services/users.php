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
use App\Framework\Utils\FilteredList\Paginator\PaginatorService;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Users\Controller\ShowOverviewController;
use App\Modules\Users\Controller\UsersController;
use App\Modules\Users\EditLocalesController;
use App\Modules\Users\EditPasswordController;
use App\Modules\Users\Entities\UserEntityFactory;
use App\Modules\Users\FormHelper\FilterFormBuilder;
use App\Modules\Users\FormHelper\FilterParameters;
use App\Modules\Users\Repositories\UserRepositoryFactory;
use App\Modules\Users\Services\AclValidator;
use App\Modules\Users\Services\ResultsList;
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
		new \App\Modules\Users\Repositories\Edge\UserMainRepository($container->get('SqlConnection')),
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
$dependencies[FilterFormBuilder::class] = DI\factory(function (ContainerInterface $container)
{
	return new FilterFormBuilder(
		$container->get(FilterParameters::class),
		$container->get(FormBuilder::class)
	);
});
$dependencies[FilterParameters::class] = DI\factory(function (ContainerInterface $container)
{
	return new FilterParameters(
		$container->get(Sanitizer::class),
		$container->get(Session::class)
	);
});
$dependencies[ResultsList::class] = DI\factory(function (ContainerInterface $container)
{
	return new ResultsList($container->get(AclValidator::class), $container->get(Config::class));
});
$dependencies[UsersController::class] = DI\factory(function (ContainerInterface $container)
{
	return new UsersController($container->get(UsersOverviewService::class), $container->get(FilterParameters::class));
});
$dependencies[ShowOverviewController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowOverviewController(
		$container->get(FilterFormBuilder::class),
		$container->get(FilterParameters::class),
		$container->get(UsersOverviewService::class),
		$container->get(PaginatorService::class),
		$container->get(ResultsList::class),
	);
});
return $dependencies;