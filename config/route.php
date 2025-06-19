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
use App\Framework\Middleware\FinalRenderMiddleware;
use App\Modules\Auth\LoginController;
use App\Modules\Auth\OAuth2Controller;
use App\Modules\Mediapool\Controller\MediaController;
use App\Modules\Mediapool\Controller\NodesController;
use App\Modules\Mediapool\Controller\ShowController;
use App\Modules\Mediapool\Controller\UploadController;
use App\Modules\Player\Controller\PlayerController;
use App\Modules\Player\Controller\PlayerIndexController;
use App\Modules\Playlists\Controller\ExportController;
use App\Modules\Playlists\Controller\ItemsController;
use App\Modules\Playlists\Controller\PlaylistsController;
use App\Modules\Playlists\Controller\ShowComposeController;
use App\Modules\Playlists\Controller\ShowDatatableController;
use App\Modules\Playlists\Controller\ShowSettingsController;
use App\Modules\Playlists\Controller\WidgetsController;
use App\Modules\Profile\Controller\EditLocalesController;
use App\Modules\Profile\Controller\EditPasswordController;
use App\Modules\Profile\Controller\ShowPasswordController;
use App\Modules\Users\Controller\ShowAdminController;
use App\Modules\Users\Controller\UsersController;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * @param string[] $controllerCallable
 */
function createControllerCallable(array $controllerCallable, ContainerInterface $container): Closure
{
	if (count($controllerCallable) !== 2)
		throw new InvalidArgumentException('Controller callable must be an array like [ControllerClass::class, \'methodName\'].');

	[$controllerClass, $methodName] = $controllerCallable;

	return function (Request $request, Response $response, array $args) use ($controllerClass, $methodName, $container)
	{
		$controller = $container->get($controllerClass);
		return $controller->{$methodName}($request, $response, $args);
	};
}
/* @var App $app */
/** @phpstan-ignore-next-line */
assert($app instanceof App);
/** @var ContainerInterface $container */
$container = $app->getContainer();

$app->get('/smil-index', createControllerCallable([PlayerIndexController::class, 'index'], $container));

$app->group('', function (RouteCollectorProxy $group) use ($container)
{
	$group->get('/', createControllerCallable([HomeController::class, 'index'], $container));
	$group->get('/legals', createControllerCallable([HomeController::class, 'legals'], $container));
	$group->get('/login', createControllerCallable([LoginController::class, 'showLogin'], $container));
	$group->post('/login', createControllerCallable([LoginController::class, 'login'], $container));
	$group->get('/logout', createControllerCallable([LoginController::class, 'logout'], $container));
	$group->get('/set-locales/{locale}', createControllerCallable([EditLocalesController::class, 'setLocales'], $container));

	$group->get('/users', createControllerCallable([\App\Modules\Users\Controller\ShowDatatableController::class, 'show'], $container));
	$group->post('/users/edit', createControllerCallable([ShowAdminController::class, 'store'], $container));
	$group->get('/users/new', createControllerCallable([ShowAdminController::class, 'newUserForm'], $container));
	$group->get('/users/edit/{UID:\d+}', createControllerCallable([ShowAdminController::class, 'editUserForm'], $container));

	// for later profile call
//	$group->get('/user/{UID:\d}', createControllerCallable([xxxx::class, 'profile'], $container));
	$group->get('/profile/settings', createControllerCallable([\App\Modules\Profile\Controller\ShowSettingsController::class, 'show'], $container));
	$group->get('/profile/password', createControllerCallable([ShowPasswordController::class, 'showForm'], $container));

	$group->get('/mediapool', createControllerCallable([ShowController::class, 'show'], $container));

	$group->get('/playlists', createControllerCallable([ShowDatatableController::class, 'show'], $container));
	$group->get('/playlists/settings/{playlist_mode:master|internal|external|multizone|channel}', createControllerCallable([ShowSettingsController::class, 'newPlaylistForm'], $container));
	$group->get('/playlists/settings/{playlist_id:\d+}', createControllerCallable([ShowSettingsController::class, 'editPlaylistForm'], $container));
	$group->delete('/playlists/settings/{playlist_id:\d+}', createControllerCallable([ShowSettingsController::class, 'delete'], $container));
	$group->post('/playlists/settings', createControllerCallable([ShowSettingsController::class, 'store'], $container));
	$group->get('/playlists/compose/{playlist_id}', createControllerCallable([ShowComposeController::class, 'show'], $container));

	$group->get('/player', createControllerCallable([\App\Modules\Player\Controller\ShowDatatableController::class, 'show'], $container));
})->add($container->get(FinalRenderMiddleware::class));

$app->group('/api', function (RouteCollectorProxy $group) use ($container)
{
	$group->get('/authorize', createControllerCallable([OAuth2Controller::class, 'authorize'], $container));
	$group->post('/token', createControllerCallable([OAuth2Controller::class, 'token'], $container));
})->add(function ($request, $handler) {return $handler->handle($request)->withHeader('Content-Type', 'text/html');});

$app->group('/async', function (RouteCollectorProxy $group) use ($container)
{
	$group->get('/users/find/{username}', createControllerCallable([UsersController::class, 'findByName'], $container));

	$group->get('/mediapool/node[/{parent_id:\d+}]', createControllerCallable([NodesController::class, 'list'], $container)); // parent_id is optional with []
	$group->post('/mediapool/node', createControllerCallable([NodesController::class, 'add'], $container));
	$group->delete('/mediapool/node', createControllerCallable([NodesController::class, 'delete'], $container));
	$group->patch('/mediapool/node', createControllerCallable([NodesController::class, 'edit'], $container));
	$group->post('/mediapool/node/move', createControllerCallable([NodesController::class, 'move'], $container));
	$group->post('/mediapool/uploadLocalFile', createControllerCallable([UploadController::class, 'uploadLocalFile'], $container));
	$group->post('/mediapool/uploadFromUrl', createControllerCallable([UploadController::class, 'uploadFromUrl'], $container));
	$group->post('/mediapool/searchStockImages', createControllerCallable([UploadController::class, 'searchStockImages'], $container));
	$group->get('/mediapool/media/list/{node_id:\d+}',createControllerCallable( [MediaController::class, 'list'], $container));
	$group->get('/mediapool/media/{media_id}', createControllerCallable([MediaController::class, 'getInfo'], $container));
	$group->post('/mediapool/media', createControllerCallable([MediaController::class, 'add'], $container));
	$group->delete('/mediapool/media', createControllerCallable([MediaController::class, 'delete'], $container));
	$group->post('/mediapool/media/edit', createControllerCallable([MediaController::class, 'edit'], $container));
	$group->post('/mediapool/media/move', createControllerCallable([MediaController::class, 'move'], $container));
	$group->post('/mediapool/media/clone', createControllerCallable([MediaController::class, 'clone'], $container));

	$group->get('/playlists/find/{playlist_mode:master|internal|external|multizone|channel}[/{playlist_name}]', createControllerCallable([PlaylistsController::class, 'findByName'], $container));
	$group->get('/playlists/find/for-player[/{playlist_name}]', createControllerCallable([PlaylistsController::class, 'findForPlayerAssignment'], $container));
	$group->delete('/playlists', createControllerCallable([PlaylistsController::class, 'delete'], $container));
	$group->put('/playlists', createControllerCallable([ExportController::class, 'export'], $container));
	$group->get('/playlists/find/{playlist_id:\d+}', createControllerCallable([PlaylistsController::class, 'findById'], $container));
	$group->get('/playlists/multizone/{playlist_id:\d+}', createControllerCallable([PlaylistsController::class, 'loadZone'], $container));
	$group->post('/playlists/multizone/{playlist_id:\d+}', createControllerCallable([PlaylistsController::class, 'saveZone'], $container));
	$group->patch('/playlists/shuffle', createControllerCallable([PlaylistsController::class, 'toggleShuffle'], $container));
	$group->patch('/playlists/picking', createControllerCallable([PlaylistsController::class, 'shufflePicking'], $container));

	$group->get('/playlists/items/load/{playlist_id:\d+}', createControllerCallable([ItemsController::class, 'loadItems'], $container));
	$group->post('/playlists/items/insert', createControllerCallable([ItemsController::class, 'insert'], $container));
	$group->delete('/playlists/items', createControllerCallable([ItemsController::class, 'delete'], $container));
	$group->patch('/playlists/items', createControllerCallable([ItemsController::class, 'updateItemOrders'], $container));
	$group->get('/playlists/item/{item_id:\d+}', createControllerCallable([ItemsController::class, 'fetch'], $container));
	$group->patch('/playlists/item', createControllerCallable([ItemsController::class, 'edit'], $container));
	$group->get('/playlists/widget/fetch/{item_id:\d+}', createControllerCallable([WidgetsController::class, 'fetch'], $container));
	$group->patch('/playlists/widget/save', createControllerCallable([WidgetsController::class, 'save'], $container));

	$group->patch('/player/playlist', createControllerCallable([PlayerController::class, 'replacePlaylist'], $container));


})->add(function ($request, $handler) {return $handler->handle($request)->withHeader('Content-Type', 'text/html');});
