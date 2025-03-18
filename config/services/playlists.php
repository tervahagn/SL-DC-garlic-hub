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
use App\Framework\Utils\DataGrid\BuildServiceLocator;
use App\Framework\Utils\DataGrid\BaseDataGridTemplateFormatter;
use App\Framework\Utils\DataGrid\FormatterServiceLocator;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Playlists\Controller\PlaylistController;
use App\Modules\Playlists\Controller\ShowComposeController;
use App\Modules\Playlists\Controller\ShowOverviewController;
use App\Modules\Playlists\Controller\ShowSettingsController;
use App\Modules\Playlists\Helper\Overview\DataGridBuilder;
use App\Modules\Playlists\Helper\Overview\DataGridFormatter;
use App\Modules\Playlists\Helper\Settings\Facade;
use App\Modules\Playlists\Helper\Settings\FilterBuilder;
use App\Modules\Playlists\Helper\Settings\Parameters;
use App\Modules\Playlists\Helper\Settings\Validator;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use App\Modules\Playlists\Services\AclValidator;
use App\Modules\Playlists\Services\PlaylistsService;
use App\Modules\Users\Services\UsersService;
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
		$container->get(UsersService::class),
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
$dependencies[Parameters::class] = DI\factory(function (ContainerInterface $container)
{
	return new Parameters(
		$container->get(Sanitizer::class),
		$container->get(Session::class)
	);
});
$dependencies[Validator::class] = DI\factory(function (ContainerInterface $container)
{
	return new Validator(
		$container->get(Translator::class),
		$container->get(Parameters::class),
	);
});
$dependencies[FilterBuilder::class] = DI\factory(function (ContainerInterface $container)
{
	return new FilterBuilder(
		$container->get(AclValidator::class),
		$container->get(Parameters::class),
		$container->get(Validator::class),
		$container->get(FormBuilder::class),
	);
});
$dependencies[Facade::class] = DI\factory(function (ContainerInterface $container)
{
	return new Facade(
		$container->get(FilterBuilder::class),
		$container->get(PlaylistsService::class),
		$container->get(Parameters::class),
		new \App\Modules\Playlists\Helper\Settings\TemplateRenderer($container->get(Translator::class))
	);
});
$dependencies[ShowSettingsController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowSettingsController(
		$container->get(Facade::class)
	);
});
$dependencies[\App\Modules\Playlists\Helper\Overview\Parameters::class] = DI\factory(function (ContainerInterface $container)
{
	return new \App\Modules\Playlists\Helper\Overview\Parameters(
		$container->get(Sanitizer::class),
		$container->get(Session::class)
	);
});
$dependencies[DataGridBuilder::class] = DI\factory(function (ContainerInterface $container)
{
	return new DataGridBuilder(
		$container->get(BuildServiceLocator::class),
		$container->get(\App\Modules\Playlists\Helper\Overview\Parameters::class),
		$container->get(Translator::class)
	);
});
$dependencies[DataGridFormatter::class] = DI\factory(function (ContainerInterface $container)
{
	return new DataGridFormatter(
		$container->get(FormatterServiceLocator::class),
		$container->get(Translator::class),
		$container->get(AclValidator::class)
	);
});
$dependencies[\App\Modules\Playlists\Helper\Overview\Facade::class] = DI\factory(function (ContainerInterface $container)
{
	return new \App\Modules\Playlists\Helper\Overview\Facade(
		$container->get(DataGridBuilder::class),
		$container->get(DataGridFormatter::class),
		$container->get(\App\Modules\Playlists\Helper\Overview\Parameters::class),
		$container->get(PlaylistsService::class)
	);
});

$dependencies[ShowOverviewController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowOverviewController(
		$container->get(\App\Modules\Playlists\Helper\Overview\Facade::class),
		$container->get(BaseDataGridTemplateFormatter::class)
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
		$container->get(Parameters::class)
	);
});


return $dependencies;
