<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Profile\Controller\EditLocalesController;
use App\Modules\Profile\Controller\EditPasswordController;
use App\Modules\Profile\Services\UserService;
use App\Modules\Users\Repositories\UserRepositoryFactory;
use App\Modules\Users\Services\UsersService;
use Psr\Container\ContainerInterface;

$dependencies = [];

$dependencies[UserService::class] = DI\factory(function (ContainerInterface $container)
{
	$factory      = $container->get(UserRepositoryFactory::class);
	$repositories = $factory->create();

	return new UserService(
		$repositories['main'],
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
	return new EditLocalesController($container->get(UserService::class));
});

return $dependencies;