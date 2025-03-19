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
use App\Framework\Utils\Datatable\BuildServiceLocator;
use App\Framework\Utils\Datatable\BaseDataGridTemplateFormatter;
use App\Framework\Utils\Datatable\FormatterServiceLocator;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Playlists\Controller\PlaylistController;
use App\Modules\Playlists\Controller\ShowComposeController;
use App\Modules\Playlists\Controller\ShowDatatableController;
use App\Modules\Playlists\Controller\ShowSettingsController;
use App\Modules\Playlists\Helper\Datatable\DatatableBuilder;
use App\Modules\Playlists\Helper\Datatable\DatatableFormatter;
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
$dependencies[\App\Modules\Playlists\Helper\Datatable\Parameters::class] = DI\factory(function (ContainerInterface $container)
{
	return new \App\Modules\Playlists\Helper\Datatable\Parameters(
		$container->get(Sanitizer::class),
		$container->get(Session::class)
	);
});
$dependencies[DatatableBuilder::class] = DI\factory(function (ContainerInterface $container)
{
	return new DatatableBuilder(
		$container->get(BuildServiceLocator::class),
		$container->get(\App\Modules\Playlists\Helper\Datatable\Parameters::class),
		$container->get(Translator::class)
	);
});
$dependencies[DatatableFormatter::class] = DI\factory(function (ContainerInterface $container)
{
	return new DatatableFormatter(
		$container->get(FormatterServiceLocator::class),
		$container->get(Translator::class),
		$container->get(AclValidator::class)
	);
});
$dependencies[\App\Modules\Playlists\Helper\Datatable\Facade::class] = DI\factory(function (ContainerInterface $container)
{
	return new \App\Modules\Playlists\Helper\Datatable\Facade(
		$container->get(DatatableBuilder::class),
		$container->get(DatatableFormatter::class),
		$container->get(\App\Modules\Playlists\Helper\Datatable\Parameters::class),
		$container->get(PlaylistsService::class)
	);
});

$dependencies[ShowDatatableController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowDatatableController(
		$container->get(\App\Modules\Playlists\Helper\Datatable\Facade::class),
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
