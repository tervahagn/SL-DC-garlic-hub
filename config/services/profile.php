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
use App\Framework\Core\Crypt;
use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\Forms\FormTemplatePreparer;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Profile\Controller\EditLocalesController;
use App\Modules\Profile\Controller\ShowPasswordController;
use App\Modules\Profile\Helper\Password\Builder;
use App\Modules\Profile\Helper\Password\Facade;
use App\Modules\Profile\Helper\Password\FormElementsCreator;
use App\Modules\Profile\Helper\Password\Parameters;
use App\Modules\Profile\Helper\Password\Validator;
use App\Modules\Profile\Services\UserService;
use App\Modules\Users\Repositories\UserRepositoryFactory;
use Psr\Container\ContainerInterface;

$dependencies = [];

$dependencies[UserService::class] = DI\factory(function (ContainerInterface $container)
{
	$factory      = $container->get(UserRepositoryFactory::class);
	$repositories = $factory->create();

	return new UserService(
		$repositories['main'],
		$repositories['tokens'],
		$container->get(Crypt::class),
		$container->get('ModuleLogger')
	);
});
$dependencies[EditLocalesController::class] = DI\factory(function (ContainerInterface $container)
{
	return new EditLocalesController($container->get(UserService::class));
});
$dependencies[Parameters::class] = DI\factory(function (ContainerInterface $container)
{
	return new Parameters(
		$container->get(Sanitizer::class),
		$container->get(Session::class)
	);
});
$dependencies[ShowPasswordController::class] = DI\factory(function (ContainerInterface $container)
{
	$validator = new Validator($container->get(Translator::class), $container->get(Parameters::class));
	$creator   = new FormElementsCreator($container->get(FormBuilder::class), $container->get(Translator::class));
	$builder   = new Builder($container->get(Parameters::class),$validator, $creator);
	$facade    = new Facade(
		$builder,
		$container->get(UserService::class),
		$container->get(Translator::class),
		$container->get(Parameters::class),
		$container->get(Config::class)
	);

	return new ShowPasswordController(
		$facade,
		$container->get(FormTemplatePreparer::class)
	);
});


return $dependencies;