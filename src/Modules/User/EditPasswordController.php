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

use App\Framework\Core\Cookie;
use App\Framework\Exceptions\UserException;
use App\Framework\User\UserService;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\SimpleCache\InvalidArgumentException;

class EditPasswordController
{
	private FormBuilder $formBuilder;
	private UserService $userService;

	public function __construct(FormBuilder $formBuilder, UserService $userService)
	{
		$this->formBuilder = $formBuilder;
		$this->userService = $userService;
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function editPassword(Request $request, Response $response): Response
	{
		$flash = $request->getAttribute('flash');
		try
		{
			$this->postActions($request);
			$flash->addMessage('success', 'User data changed');
		}
		catch(UserException $e)
		{
			$flash->addMessage('error', $e->getMessage());
		}

		return $response->withHeader('Location', '/user/edit')->withStatus(302);
	}

	/**
	 * @throws Exception
	 * @throws InvalidArgumentException|\Doctrine\DBAL\Exception
	 */
	public function showForm(Request $request, Response $response): Response
	{
		$translator = $request->getAttribute('translator');

		$formElements   = [];
		$hiddenElements = [];

		foreach ($this->createPasswordForm($request) as $key => $element)
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
				'LANG_ELEMENT_NAME'  => $translator->translate($key, 'user'),
				'ELEMENT_MUST_FIELD' => '', //$element->getAttribute('required') ? '*' : '',
				'HTML_ELEMENT'       => $this->formBuilder->renderField($element)
			];
		}

		$data = [
				'main_layout' => [
					'LANG_PAGE_TITLE' => $translator->translate('options', 'user'),
					'additional_css' => ['/css/user/options.css']
				],
				'this_layout' => [
					'template' => 'utils/edit', // Template-name
					'data' => [
						'LANG_PAGE_HEADER' => 'User Options',
						'SITE' => '/user/edit/password',
						'element_hidden' => $hiddenElements,
						'form_element' => $formElements,
						'form_button' => [
							[
								'ELEMENT_BUTTON_TYPE' => 'submit',
								'ELEMENT_BUTTON_NAME' => 'submit',
								'LANG_ELEMENT_BUTTON' => $translator->translate('save', 'main')
							]
					]
				]
			]
		];
		$response->getBody()->write(serialize($data));

		return $response->withHeader('Content-Type', 'text/html');
	}

	/**
	 * @throws UserException
	 * @throws \Doctrine\DBAL\Exception
	 */
	private function postActions(Request $request): void
	{
		$session  = $request->getAttribute('session');
		$postData = $request->getParsedBody();

		if ($postData['csrf_token'] !== $session->get('csrf_token'))
			throw new UserException('CSRF Token mismatch');

		if (strlen($postData['edit_password']) < 8)
			throw new UserException('Password too small');

		if ($postData['edit_password'] !== $postData['repeat_password'])
			throw new UserException('Password not same');

		if ($this->userService->updatePassword($session->get('user')['UID'], $postData['edit_password']) !== 1)
			throw new UserException('User data could not be changed');

	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	private function createPasswordForm(Request $request): array
	{
		$form = [];
		$rules = ['required' => true, 'minlength' => 8];

		$form['edit_password'] = $this->formBuilder->createField([
			'type' => FieldType::PASSWORD,
			'id' => 'edit_password',
			'name' => 'edit_password',
			'value' => '',
			'rules' => $rules,
			'default_value' => ''
		]);
		$form['repeat_password'] = $this->formBuilder->createField([
			'type' => FieldType::PASSWORD,
			'id' => 'repeat_password',
			'name' => 'repeat_password',
			'rules' => $rules,
			'default_value' => ''
		]);

		$form['csrf_token'] = $this->formBuilder->createField([
			'type' => FieldType::CSRF,
			'id' => 'csrf_token',
			'name' => 'csrf_token',
		]);

		return $form;
	}


}