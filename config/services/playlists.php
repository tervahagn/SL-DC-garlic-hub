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

use App\Framework\Core\Config\Config;
use App\Framework\Core\Sanitizer;
use App\Framework\User\UserService;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Playlists\Controller\SettingsController;
use App\Modules\Playlists\FormHelper\SettingsFormBuilder;
use App\Modules\Playlists\FormHelper\SettingsValidator;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use App\Modules\Playlists\Services\AclValidator;
use App\Modules\Playlists\Services\PlaylistsService;
use Psr\Container\ContainerInterface;

$dependencies = [];

$dependencies[PlaylistsRepository::class] = DI\factory(function (ContainerInterface $container)
{
	return new PlaylistsRepository($container->get('SqlConnection'));
});

$dependencies[AclValidator::class] = DI\factory(function (ContainerInterface $container)
{
	return new AclValidator(
		'playlists',
		$container->get(UserService::class),
		$container->get(Config::class),
	);
});

$dependencies[PlaylistsService::class] = DI\factory(function (ContainerInterface $container)
{
	return new PlaylistsService(
		$container->get(PlaylistsRepository::class),
		$container->get(AclValidator::class),
		$container->get('ModuleLogger')
	);
});
$dependencies[SettingsFormBuilder::class] = DI\factory(function (ContainerInterface $container)
{
	return new SettingsFormBuilder(
		$container->get(AclValidator::class),
		$container->get(FormBuilder::class)
	);
});
$dependencies[SettingsController::class] = DI\factory(function (ContainerInterface $container)
{
	return new SettingsController(
		$container->get(SettingsFormBuilder::class),
		new SettingsValidator($container->get(Sanitizer::class)),
		$container->get(PlaylistsService::class)
	);
});


return $dependencies;
