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


namespace App\Modules\Profile\Controller;

use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Utils\Forms\FormTemplatePreparer;
use App\Modules\Profile\Helper\Password\Facade;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Slim\Flash\Messages;

class ShowPasswordController
{
	private readonly Facade $facade;
	private readonly FormTemplatePreparer $formElementPreparer;
	private Messages $flash;

	public function __construct(Facade $facade, FormTemplatePreparer $formElementPreparer)
	{
		$this->facade = $facade;
		$this->formElementPreparer = $formElementPreparer;
	}

	public function showForm(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$this->initFacade($request);
		return $this->outputRenderedForm($response);
	}

	public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		/** @var array<string,mixed> $post */
		$post = $request->getParsedBody();

		$this->initFacade($request);
		$errors = $this->facade->configureUserFormParameter($post);
		foreach ($errors as $errorText)
		{
			$this->flash->addMessageNow('error', $errorText);
		}

		if (!empty($errors))
			return $this->outputRenderedForm($response, $post);

		$id = $this->facade->storePassword();
		if ($id > 0)
		{
			$this->flash->addMessage('success', 'New password successfully stored.');
			return $response->withHeader('Location', '/')->withStatus(302);
		}
		else
		{
			$errors = $this->facade->getUserServiceErrors();
			foreach ($errors as $errorText)
			{
				$this->flash->addMessageNow('error', $errorText);
			}
		}

		return $this->outputRenderedForm($response, $post);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	private function outputRenderedForm(ResponseInterface $response): ResponseInterface
	{
		$dataSections = $this->facade->prepareUITemplate();
		$templateData = $this->formElementPreparer->prepareUITemplate($dataSections);

		$response->getBody()->write(serialize($templateData));
		return $response->withHeader('Content-Type', 'text/html')->withStatus(200);
	}


	private function initFacade(ServerRequestInterface $request): void
	{
		$this->flash      = $request->getAttribute('flash');
		$this->facade->init($request->getAttribute('translator'), $request->getAttribute('session'));
	}

}