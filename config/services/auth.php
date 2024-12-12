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
use App\Framework\OAuth2\AuthCodesRepository;
use App\Framework\OAuth2\ClientsRepository;
use App\Framework\OAuth2\OAuth2Service;
use App\Framework\OAuth2\ScopeRepository;
use App\Framework\OAuth2\TokensRepository;
use App\Framework\User\UserService;
use App\Modules\Auth\AuthService;
use App\Modules\Auth\LoginController;
use App\Modules\Auth\OAuth2Controller;
use Defuse\Crypto\Key;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;

$dependencies = [];

$dependencies[AuthService::class] = DI\factory(function (ContainerInterface $container)
{
	return new AuthService($container->get(UserService::class));
});
$dependencies[LoginController::class] = DI\factory(function (ContainerInterface $container)
{
	return new LoginController($container->get(AuthService::class), $container->get(LoggerInterface::class));
});

$dependencies['AuthorizationServer'] = DI\factory(function (ContainerInterface $container) {
	$clientRepository      = new ClientsRepository($container->get('SqlConnection'));
	$accessTokenRepository = new TokensRepository($container->get('SqlConnection'));
	$authCodeRepository    = new AuthCodesRepository($container->get('SqlConnection'));
	$scopeRepository       = new ScopeRepository($container->get('SqlConnection'));

	$config        = $container->get(Config::class);
	$keysDir       = $config->getPaths('keysDir');
	$privateKey    = new CryptKey('file:/'.$keysDir.'/private.key');
	$encryptionKey = Key::loadFromAsciiSafeString(file_get_contents($keysDir.'/encryption.key'));

	$server = new AuthorizationServer(
		$clientRepository,
		$accessTokenRepository,
		$scopeRepository,
		$privateKey,
		$encryptionKey
	);

	$server->enableGrantType(
		new \League\OAuth2\Server\Grant\ClientCredentialsGrant(),
		new \DateInterval('PT1H') // 1 day
	);
	return $server;
});
$dependencies[OAuth2Controller::class] = DI\factory(function (ContainerInterface $container)
{
	return new OAuth2Controller($container->get(AuthService::class),
		$container->get(OAuth2Service::class),
		$container->get(LoggerInterface::class),
		$container->get('AuthorizationServer')
	);
});

return $dependencies;