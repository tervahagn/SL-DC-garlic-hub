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
use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\User\UserService;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Playlists\Controller\ListFilterController;
use App\Modules\Playlists\Controller\SettingsController;
use App\Modules\Playlists\FormHelper\ListFilterFormBuilder;
use App\Modules\Playlists\FormHelper\ListFilterParameters;
use App\Modules\Playlists\FormHelper\SettingsParameters;
use App\Modules\Playlists\FormHelper\SettingsFormBuilder;
use App\Modules\Playlists\FormHelper\SettingsValidator;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use App\Modules\Playlists\Services\AclValidator;
use App\Modules\Playlists\Services\PlaylistsEditService;
use App\Modules\Playlists\Services\PlaylistsFilterListService;
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

$dependencies[PlaylistsEditService::class] = DI\factory(function (ContainerInterface $container)
{
	return new PlaylistsEditService(
		$container->get(PlaylistsRepository::class),
		$container->get(AclValidator::class),
		$container->get('ModuleLogger')
	);
});
$dependencies[SettingsParameters::class] = DI\factory(function (ContainerInterface $container)
{
	return new SettingsParameters(
		$container->get(Sanitizer::class),
		$container->get(Session::class)
	);
});
$dependencies[SettingsValidator::class] = DI\factory(function (ContainerInterface $container)
{
	return new SettingsValidator(
		$container->get(Translator::class),
		$container->get(SettingsParameters::class),
	);
});
$dependencies[SettingsFormBuilder::class] = DI\factory(function (ContainerInterface $container)
{
	return new SettingsFormBuilder(
		$container->get(AclValidator::class),
		$container->get(SettingsParameters::class),
		$container->get(SettingsValidator::class),
		$container->get(FormBuilder::class)
	);
});
$dependencies[SettingsController::class] = DI\factory(function (ContainerInterface $container)
{
	return new SettingsController(
		$container->get(SettingsFormBuilder::class),
		$container->get(SettingsParameters::class),
		$container->get(PlaylistsEditService::class)
	);
});
$dependencies[PlaylistsFilterListService::class] = DI\factory(function (ContainerInterface $container)
{
	return new PlaylistsFilterListService(
		$container->get(PlaylistsRepository::class),
		$container->get(AclValidator::class),
		$container->get('ModuleLogger')
	);
});
$dependencies[ListFilterParameters::class] = DI\factory(function (ContainerInterface $container)
{
	return new ListFilterParameters(
		$container->get(Sanitizer::class),
		$container->get(Session::class)
	);
});

$dependencies[ListFilterFormBuilder::class] = DI\factory(function (ContainerInterface $container)
{
	return new ListFilterFormBuilder(
		$container->get(AclValidator::class),
		$container->get(ListFilterParameters::class),
		$container->get(FormBuilder::class)
	);
});

$dependencies[ListFilterController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ListFilterController(
		$container->get(ListFilterFormBuilder::class),
		$container->get(ListFilterParameters::class),
		$container->get(PlaylistsFilterListService::class)
	);
});
return $dependencies;
