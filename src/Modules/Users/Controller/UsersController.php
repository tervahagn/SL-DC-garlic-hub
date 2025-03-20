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

namespace App\Modules\Users\Controller;

use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FormParameters\ScalarType;
use App\Modules\Users\Helper\Datatable\Parameters;
use App\Modules\Users\Services\UsersDatatableService;
use App\Modules\Users\UserStatus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UsersController
{
	private UsersDatatableService $usersService;
	private readonly Parameters $parameters;

	public function __construct(UsersDatatableService $usersService, Parameters $parameters)
	{
		$this->usersService = $usersService;
		$this->parameters = $parameters;
	}


	/**
	 * @throws ModuleException
	 */
	public function findByName(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		// we want only registered user and higher
		$this->parameters->addParameter(Parameters::PARAMETER_FROM_STATUS, ScalarType::INT, UserStatus::REGISTERED->value);

		$this->parameters->setUserInputs($args);
		$this->parameters->parseInputAllParameters();

		$session = $request->getAttribute('session');
		$this->usersService->setUID($session->get('user')['UID']);
		$this->usersService->loadUsersForOverview($this->parameters);
		$results = [];
		foreach ($this->usersService->getCurrentFilterResults() as $value)
		{
			$results[] = ['id' => $value['UID'], 'name' => $value['username']];
		}

		$response->getBody()->write(json_encode($results));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}

}