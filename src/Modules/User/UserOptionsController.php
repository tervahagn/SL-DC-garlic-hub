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


namespace App\Modules\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserOptionsController
{

	public function __construct()
	{
	}

	/**
	 * @throws \Exception
	 */
	public function editUser(Request $request, Response $response): Response
	{
		$form = $this->getUserForm();

		if ($request->getMethod() === 'POST')
		{
			$postData = $request->getParsedBody();
			$form->setData($postData);

			if ($form->isValid())
			{
				// Process valid data here (e.g., save to DB)
				// $data = $form->getData();
			}
		}

		/** @var Element $element */
		$formElements = [];
		$formViewHelper = new FormText();

		foreach ($form->getElements() as $element)
		{
			$formElements[] = [
				'HTML_ELEMENT_ID'    => $element->getAttribute('id'),
				'LANG_ELEMENT_NAME'  => $element->getLabel(),
				'ELEMENT_MUST_FIELD' => $element->getAttribute('required') ? '*' : '',
				'HTML_ELEMENT'       => $formViewHelper->render($element)
			];
		}
		$hiddenElements = [];
		foreach ($form->getElements() as $element)
		{
			if ($element instanceof Element\Hidden)
			{
				$hiddenElements[] = [
					'HIDDEN_ELEMENT_NAME' => $element->getName(),
					'HIDDEN_ELEMENT_VALUE' => $element->getValue()
				];
			}
		}


		$data = [
				'main_layout' => [
					'LANG_PAGE_TITLE' => 'Garlic Hub - UserOptions',
					'error_messages' => '$error',
					'ADDITIONAL_CSS' => ['/css/user/options.css']
				],
				'this_layout' => [
					'template' => 'utils/edit', // Template-name
					'data' => [
						'LANG_PAGE_HEADER' => 'User Options',
						'SITE' => '/user/edit',
						'element_hidden' => $hiddenElements,
						'form_element' => $formElements,
						'form_button' => [
							[
								'ELEMENT_BUTTON_NAME' => 'submit',
								'LANG_ELEMENT_BUTTON' => 'Save'
							]
					]
				]
			]
		];
		$response->getBody()->write(serialize($data));

		return $response->withHeader('Content-Type', 'text/html');
	}

	/**
	 * @throws \Exception
	 */
	private function getUserForm(): Form
	{
		$form = new Form('user_form');
		$form->add([
					 'name'       => 'email',
					 'type'       => Element\Email::class,
					 'options'    => ['label' => 'E-Mail'],
					 'attributes' => ['id' => 'email', 'required' => true ]
		]);

		$form->add([
					 'name'       => 'password',
					 'type'       => Element\Password::class,
					 'options'    => ['label' => 'Passwort'],
					 'attributes' => ['id' => 'password', 'required' => true]
		]);

		$form->add([
					 'name'       => 'password_confirm',
					 'type'       => Element\Password::class,
					 'options'    => ['label' => 'Passwort bestätigen'],
					 'attributes' => ['id' => 'password_confirm','required' => true]
		]);

		$form->add([
					 'name'       => 'csrf_token',
					 'type'       => Element\Hidden::class,
					 'attributes' => ['value' => bin2hex(random_bytes(32))]
		]);

		return $form;
	}
}