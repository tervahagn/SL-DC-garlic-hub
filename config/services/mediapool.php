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
use App\Modules\Mediapool\Controller\NodesController;
use App\Modules\Mediapool\Controller\UploadController;
use App\Modules\Mediapool\Repositories\FilesRepository;
use App\Modules\Mediapool\Repositories\NodesRepository;
use App\Modules\Mediapool\Repositories\QueueRepository;
use App\Modules\Mediapool\Services\NodesService;
use App\Modules\Mediapool\Services\UploadService;
use App\Modules\Mediapool\Utils\MediaHandlerFactory;
use App\Modules\Mediapool\Utils\MimeTypeDetector;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Psr\Container\ContainerInterface;

$dependencies = [];
$dependencies[NodesService::class] = DI\factory(function (ContainerInterface $container)
{
	return new NodesService(
		new NodesRepository($container->get('SqlConnection'))
	);
});
$dependencies[NodesController::class] = DI\factory(function (ContainerInterface $container)
{
	return new NodesController($container->get(NodesService::class));
});

$dependencies[MediaHandlerFactory::class] = DI\factory(function (ContainerInterface $container)
{
	return new MediaHandlerFactory(
		$container->get(Config::class),
		$container->get('LocalFileSystem'),
		new ImageManager(new Driver())
	);
});

$dependencies[UploadService::class] = DI\factory(function (ContainerInterface $container)
{
	return new UploadService(
		$container->get(MediaHandlerFactory::class),
		new FilesRepository($container->get('SqlConnection')),
		new MimeTypeDetector(),
		$container->get('ModuleLogger')
	);
});

$dependencies[UploadController::class] = DI\factory(function (ContainerInterface $container)
{
	return new UploadController($container->get(UploadService::class));
});


return $dependencies;
