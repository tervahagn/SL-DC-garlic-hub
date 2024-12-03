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

use App\Framework\User\UserService;
use App\Modules\Auth\AuthService;
use App\Modules\Auth\LoginController;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

$dependencies = [];

$dependencies[AuthService::class] = DI\factory(function (ContainerInterface $container)
{
	return new AuthService($container->get(UserService::class));
});
$dependencies[LoginController::class] = DI\factory(function (ContainerInterface $container)
{
	return new LoginController($container->get(AuthService::class), $container->get(LoggerInterface::class));
});


return $dependencies;