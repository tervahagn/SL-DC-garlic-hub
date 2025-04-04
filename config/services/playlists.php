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

use App\Framework\Core\Acl\AclHelper;
use App\Framework\Core\Config\Config;
use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\Datatable\BuildService;
use App\Framework\Utils\Datatable\DatatableTemplatePreparer;
use App\Framework\Utils\Datatable\PrepareService;
use App\Framework\Utils\Forms\FormTemplatePreparer;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Mediapool\Controller\SelectorController;
use App\Modules\Mediapool\Services\MediaService;
use App\Modules\Playlists\Controller\ItemsController;
use App\Modules\Playlists\Controller\PlaylistsController;
use App\Modules\Playlists\Controller\ShowComposeController;
use App\Modules\Playlists\Controller\ShowDatatableController;
use App\Modules\Playlists\Controller\ShowSettingsController;
use App\Modules\Playlists\Helper\Compose\RightsChecker;
use App\Modules\Playlists\Helper\Compose\UiTemplatesPreparer;
use App\Modules\Playlists\Helper\Datatable\ControllerFacade;
use App\Modules\Playlists\Helper\Datatable\DatatableBuilder;
use App\Modules\Playlists\Helper\Datatable\DatatablePreparer;
use App\Modules\Playlists\Helper\Settings\Builder;
use App\Modules\Playlists\Helper\Settings\Facade;
use App\Modules\Playlists\Helper\Settings\FormElementsCreator;
use App\Modules\Playlists\Helper\Settings\Parameters;
use App\Modules\Playlists\Helper\Settings\Validator;
use App\Modules\Playlists\Repositories\ItemsRepository;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use App\Modules\Playlists\Services\AclValidator;
use App\Modules\Playlists\Services\ItemsService;
use App\Modules\Playlists\Services\PlaylistsDatatableService;
use App\Modules\Playlists\Services\PlaylistsService;
use Psr\Container\ContainerInterface;

$dependencies = [];

$dependencies[PlaylistsRepository::class] = DI\factory(function (ContainerInterface $container)
{
	return new PlaylistsRepository($container->get('SqlConnection'));
});
$dependencies[ItemsRepository::class] = DI\factory(function (ContainerInterface $container)
{
	return new ItemsRepository($container->get('SqlConnection'));
});

$dependencies[AclValidator::class] = DI\factory(function (ContainerInterface $container)
{
	return new AclValidator($container->get(AclHelper::class));
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
$dependencies[Builder::class] = DI\factory(function (ContainerInterface $container)
{
	return new Builder(
		$container->get(AclValidator::class),
		$container->get(Parameters::class),
		$container->get(Validator::class),
		new FormElementsCreator($container->get(FormBuilder::class), $container->get(Translator::class)),
	);
});
$dependencies[Facade::class] = DI\factory(function (ContainerInterface $container)
{
	return new Facade(
		$container->get(Builder::class),
		$container->get(PlaylistsService::class),
		$container->get(Parameters::class)
	);
});
$dependencies[ShowSettingsController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowSettingsController(
		$container->get(Facade::class),
		$container->get(FormTemplatePreparer::class)
	);
});

// Datatable
$dependencies[PlaylistsDatatableService::class] = DI\factory(function (ContainerInterface $container)
{
	return new PlaylistsDatatableService(
		$container->get(PlaylistsRepository::class),
		$container->get(\App\Modules\Playlists\Helper\Datatable\Parameters::class),
		$container->get(AclValidator::class),
		$container->get('ModuleLogger')
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
		$container->get(BuildService::class),
		$container->get(\App\Modules\Playlists\Helper\Datatable\Parameters::class),
		$container->get(AclValidator::class)
	);
});
$dependencies[DatatablePreparer::class] = DI\factory(function (ContainerInterface $container)
{
	return new DatatablePreparer(
		$container->get(PrepareService::class),
		$container->get(AclValidator::class),
		$container->get(\App\Modules\Playlists\Helper\Datatable\Parameters::class)
	);
});
$dependencies[ControllerFacade::class] = DI\factory(function (ContainerInterface $container)
{
	return new ControllerFacade(
		$container->get(DatatableBuilder::class),
		$container->get(DatatablePreparer::class),
		$container->get(PlaylistsDatatableService::class)
	);
});

$dependencies[ShowDatatableController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowDatatableController(
		$container->get(ControllerFacade::class),
		$container->get(DatatableTemplatePreparer::class)
	);
});
$dependencies[ShowComposeController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ShowComposeController(
		$container->get(PlaylistsService::class),
		new UiTemplatesPreparer(
			$container->get(Translator::class),
			new RightsChecker(
				$container->get(Translator::class), $container->get(AclValidator::class))
		)
	);
});
$dependencies[PlaylistsController::class] = DI\factory(function (ContainerInterface $container)
{
	return new PlaylistsController(
		$container->get(PlaylistsService::class),
		$container->get(PlaylistsDatatableService::class),
		$container->get(\App\Modules\Playlists\Helper\Datatable\Parameters::class)
	);
});

$dependencies[SelectorController::class] = DI\factory(function (ContainerInterface $container)
{
	return new SelectorController(
		$container->get(Config::class)
	);
});

// Items
$dependencies[ItemsController::class] = DI\factory(function (ContainerInterface $container)
{
	return new ItemsController(
		$container->get(ItemsService::class),
		$container->get(MediaService::class)
	);
});
$dependencies[ItemsService::class] = DI\factory(function (ContainerInterface $container)
{
	return new ItemsService(
		$container->get(ItemsRepository::class),
		$container->get(PlaylistsRepository::class),
		$container->get(AclValidator::class),
		$container->get('ModuleLogger')
	);
});

return $dependencies;
