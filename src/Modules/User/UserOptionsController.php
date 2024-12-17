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

use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\SimpleCache\InvalidArgumentException;

class UserOptionsController
{
	private FormBuilder $formBuilder;
	private Translator $translator;

	public function __construct(FormBuilder $formBuilder)
	{
		$this->formBuilder = $formBuilder;
	}

	/**
	 * @throws Exception
	 * @throws InvalidArgumentException
	 */
	public function editUser(Request $request, Response $response): Response
	{
		$this->translator = $request->getAttribute('translator');
		$error = '';
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
					'HIDDEN_HTML_ELEMENT'        => $this->formBuilder->renderField($element)
				];
				continue;
			}

			$formElements[] = [
				'HTML_ELEMENT_ID'    => $element->getId(),
				'LANG_ELEMENT_NAME'  => $this->translator->translate($key, 'user'),
				'ELEMENT_MUST_FIELD' => '', //$element->getAttribute('required') ? '*' : '',
				'HTML_ELEMENT'       => $this->formBuilder->renderField($element)
			];
		}

		$data = [
				'main_layout' => [
					'LANG_PAGE_TITLE' => $this->translator->translate('options', 'user'),
					'error_messages' => $error,
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
								'LANG_ELEMENT_BUTTON' => $this->translator->translate('save', 'main')
							]
					]
				]
			]
		];
		$response->getBody()->write(serialize($data));

		return $response->withHeader('Content-Type', 'text/html');
	}

	/**
	 * @throws Exception
	 */
	private function getUserForm(Request $request): array
	{
		$form = [];
		$rules = ['required' => true, 'minlength' => 8];

		$form['edit_email'] = $this->formBuilder->createField([
			'type' => FieldType::EMAIL,
			'id' => 'email',
			'name' => 'email',
			'rules' => $rules,
			'default_value' => ''
		]);
		$form['edit_password'] = $this->formBuilder->createField([
			'type' => FieldType::PASSWORD,
			'id' => 'password',
			'name' => 'password',
			'rules' => $rules,
			'default_value' => ''
		]);
		$form['repeat_password'] = $this->formBuilder->createField([
			'type' => FieldType::PASSWORD,
			'id' => 'password_repeat',
			'name' => 'password_repeat',
			'rules' => $rules,
			'default_value' => ''
		]);

		$form['csrf_token'] = $this->formBuilder->createField([
			'type' => FieldType::CSRF,
			'id' => 'csrf_token',
			'name' => 'csrf_token',
		]);

		$session = $request->getAttribute('session');
		$session->set('csrf_token', $form['csrf_token']->getValue());

		return $form;
	}
}