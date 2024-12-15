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

use App\Framework\Utils\Html\FieldsFactory;
use App\Framework\Utils\Html\FieldsRenderFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserOptionsController
{
	private FieldsFactory $fieldsFactory;
	private FieldsRenderFactory $fieldRenderFactory;

	public function __construct(FieldsFactory $fieldsFactory, FieldsRenderFactory $fieldsRenderFactory)
	{
		$this->fieldsFactory      = $fieldsFactory;
		$this->fieldRenderFactory = $fieldsRenderFactory;

	}

	/**
	 * @throws \Exception
	 */
	public function editUser(Request $request, Response $response): Response
	{

		if ($request->getMethod() === 'POST')
		{
			$postData = $request->getParsedBody();
			{
				// Process valid data here (e.g., save to DB)
				// $data = $form->getData();
			}
		}

		$formElements   = [];
		$hiddenElements = [];

		foreach ($this->getUserForm($request) as $key => $element)
		{
			if ($key === 'csrf_token')
			{
				$hiddenElements[] = [
					'HIDDEN_HTML_ELEMENT'        => $this->fieldRenderFactory->getRenderer($element)
				];
				continue;
			}

			$formElements[] = [
				'HTML_ELEMENT_ID'    => $element->getId(),
				'LANG_ELEMENT_NAME'  => $element->getName(),
				'ELEMENT_MUST_FIELD' => '', //$element->getAttribute('required') ? '*' : '',
				'HTML_ELEMENT'       => $this->fieldRenderFactory->getRenderer($element)
			];
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
	private function getUserForm(Request $request): array
	{
		$form = [];
		$rules = ['required' => true, 'minlength' => 8];
		$form['email'] = $this->fieldsFactory->createEmailField('email')->setValidationRules($rules);
		$form['password'] = $this->fieldsFactory->createPasswordField('password')->setValidationRules($rules);
		$form['password_repeat'] = $this->fieldsFactory->createPasswordField('password_repeat')->setValidationRules($rules);
		$csrfToken = bin2hex(random_bytes(32));
		$session = $request->getAttribute('session');
		$session->set('csrf_token', $csrfToken);
		$form['csrf_token'] = $this->fieldsFactory->createCsrfTokenField('csrf_token','csrf_token', $csrfToken);

		return $form;
	}
}