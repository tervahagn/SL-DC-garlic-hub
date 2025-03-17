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

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\FilteredList\Paginator\PaginationManager;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Modules\Users\Helper\Overview\Facade;
use App\Modules\Users\Helper\Overview\FormBuilder;
use App\Modules\Users\Helper\Overview\Parameters;
use App\Modules\Users\Services\ResultsList;
use App\Modules\Users\Services\UsersOverviewService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;

class ShowOverviewController
{
	private readonly Facade $facade;
	private readonly Parameters $parameters;
	private readonly UsersOverviewService $usersService;
	private readonly PaginationManager $paginatorService;
	private readonly ResultsList $resultsList;
	private Translator $translator;
	private Session $session;
	private Messages $flash;
	public function __construct(FormBuilder $formBuilder, Parameters $parameters, UsersOverviewService $usersService, PaginationManager $paginatorService, ResultsList $resultsList)
	{
		$this->formBuilder      = $formBuilder;
		$this->parameters       = $parameters;
		$this->usersService     = $usersService;
		$this->paginatorService = $paginatorService;
		$this->resultsList      = $resultsList;
	}

	public function show(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$this->parameters->setUserInputs($_GET);
		$this->parameters->parseInputFilterAllUsers();

		$this->setImportantAttributes($request);
		$this->usersService->loadUsersForOverview($this->parameters);

		$data = $this->buildForm();

		$response->getBody()->write(serialize($data));
		return $response->withHeader('Content-Type', 'text/html');
	}


	private function setImportantAttributes(ServerRequestInterface $request): void
	{
		$this->translator = $request->getAttribute('translator');
		$this->session    = $request->getAttribute('session');
		$this->usersService->setUID($this->session->get('user')['UID']);
		$this->flash      = $request->getAttribute('flash');
	}

}