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
use App\Framework\Utils\FilteredList\Paginator\PaginatorService;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Playlists\Controller\PlaylistController;
use App\Modules\Playlists\Controller\ShowComposeController;
use App\Modules\Playlists\Controller\ShowOverviewController;
use App\Modules\Playlists\Controller\ShowSettingsController;
use App\Modules\Playlists\FormHelper\FilterFormBuilder;
use App\Modules\Playlists\FormHelper\FilterParameters;
use App\Modules\Playlists\FormHelper\SettingsFormBuilder;
use App\Modules\Playlists\FormHelper\SettingsParameters;
use App\Modules\Playlists\FormHelper\SettingsValidator;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use App\Modules\Playlists\Services\AclValidator;
use App\Modules\Playlists\Services\PlaylistsOverviewService;
use App\Modules\Playlists\Services\PlaylistsService;
use App\Modules\Playlists\Services\ResultList;
use App\Modules\Users\Services\UserService;
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
$dependencies[ShowSettingsController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowSettingsController(
		$container->get(SettingsFormBuilder::class),
		$container->get(SettingsParameters::class),
		$container->get(PlaylistsService::class)
	);
});
$dependencies[FilterParameters::class] = DI\factory(function (ContainerInterface $container)
{
	return new FilterParameters(
		$container->get(Sanitizer::class),
		$container->get(Session::class)
	);
});

$dependencies[FilterFormBuilder::class] = DI\factory(function (ContainerInterface $container)
{
	return new FilterFormBuilder(
		$container->get(FilterParameters::class),
		$container->get(FormBuilder::class)
	);
});
$dependencies[ResultList::class] = DI\factory(function (ContainerInterface $container)
{
	return new ResultList($container->get(AclValidator::class), $container->get(Config::class));
});
$dependencies[ShowOverviewController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowOverviewController(
		$container->get(FilterFormBuilder::class),
		$container->get(FilterParameters::class),
		$container->get(PlaylistsService::class),
		$container->get(PaginatorService::class),
		$container->get(ResultList::class),
	);
});
$dependencies[ShowComposeController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowComposeController(
		$container->get(PlaylistsService::class),
	);
});
$dependencies[PlaylistController::class] = DI\factory(function (ContainerInterface $container)
{
	return new PlaylistController(
		$container->get(PlaylistsService::class),
		$container->get(FilterParameters::class)
	);
});


return $dependencies;
