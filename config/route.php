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

use App\Controller\HomeController;
use App\Modules\Auth\LoginController;
use App\Modules\Auth\OAuth2Controller;
use App\Modules\Mediapool\Controller\MediaController;
use App\Modules\Mediapool\Controller\NodesController;
use App\Modules\Mediapool\Controller\ShowController;
use App\Modules\Mediapool\Controller\UploadController;
use App\Modules\User\EditLocalesController;
use App\Modules\User\EditPasswordController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/* @var App $app */
/** @phpstan-ignore-next-line */
assert($app instanceof App);
$container = $app->getContainer();

$app->get('/', [HomeController::class, 'index']);
$app->get('/login', [LoginController::class, 'showLogin']);
$app->post('/login', [LoginController::class, 'login']);
$app->get('/logout', [LoginController::class, 'logout']);
$app->get('/set-locales/{locale}', [EditLocalesController::class, 'setLocales']);

$app->group('/api', function (RouteCollectorProxy $group) {
	$group->get('/authorize', [OAuth2Controller::class, 'authorize']);
	$group->post('/token', [OAuth2Controller::class, 'token']);
});

$app->get('/user/edit', [EditPasswordController::class, 'showForm']);
$app->post('/user/edit/password', [EditPasswordController::class, 'editPassword']);
$app->group('/api', function (RouteCollectorProxy $group)
{
	$group->get('/user/edit', [EditPasswordController::class, 'showForm']);
	$group->post('/user/edit/password', [EditPasswordController::class, 'editPassword']);
});

$app->get('/mediapool', [ShowController::class, 'show']);
$app->group('/async', function (RouteCollectorProxy $group)
{
	$group->get('/mediapool/node[/{parent_id}]', [NodesController::class, 'list']); // parent_id is optional with []
	$group->post('/mediapool/node', [NodesController::class, 'add']);
	$group->delete('/mediapool/node', [NodesController::class, 'delete']);
	$group->patch('/mediapool/node', [NodesController::class, 'edit']);
	$group->post('/mediapool/upload', [UploadController::class, 'upload']);
	$group->get('/mediapool/media/{node_id}', [MediaController::class, 'list']);
	$group->post('/mediapool/media', [MediaController::class, 'add']);
	$group->delete('/mediapool/media', [MediaController::class, 'delete']);
});
