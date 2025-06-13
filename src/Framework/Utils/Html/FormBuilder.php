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

namespace App\Framework\Utils\Html;

use App\Framework\Core\Session;
use App\Framework\Exceptions\FrameworkException;
use Exception;

class FormBuilder
{
	private FieldsFactory $fieldsFactory;
	private FieldsRenderFactory $fieldsRenderFactory;
	private Session $session;

	public function __construct(FieldsFactory $fieldsFactory, FieldsRenderFactory $fieldsRenderFactory, Session $session)
	{
		$this->fieldsFactory       = $fieldsFactory;
		$this->fieldsRenderFactory = $fieldsRenderFactory;
		$this->session             = $session;
	}

	/**
	 * @param list<array<string,FieldInterface>> $formFields
	 * @return array{hidden:list<array<string,string>>, visible: list<array<string,string>>}
	 */
	public function prepareForm(array $formFields): array
	{
		$hidden = [];
		$visible = [];

		/** @var FieldInterface $element */
		foreach ($formFields as $element)
		{
			if ($element->getType() === FieldType::HIDDEN || $element->getType() === FieldType::CSRF)
			{
				$hidden[] = [
					'HIDDEN_HTML_ELEMENT'        => $this->renderField($element)
				];
				continue;
			}

			$visible[] = [
				'HTML_ELEMENT_ID'    => $element->getId(),
				'LANG_ELEMENT_NAME'  => $element->getLabel(),
				'ELEMENT_MUST_FIELD' => '', //$element->getAttribute('required') ? '*' : '',
				'HTML_ELEMENT'       => $this->renderField($element)
			];
		}

		return ['hidden' => $hidden, 'visible' => $visible];
	}


	/**
	 * @param array<string,mixed> $attributes
	 * @throws FrameworkException
	 * @throws Exception
	 */
	public function createField(array $attributes = []): FieldInterface
	{
		return match ($attributes['type']) {
			FieldType::TEXT         => $this->fieldsFactory->createTextField($attributes),
			FieldType::NUMBER       => $this->fieldsFactory->createNumberField($attributes),
			FieldType::DROPDOWN     => $this->fieldsFactory->createDropdownField($attributes),
			FieldType::AUTOCOMPLETE => $this->fieldsFactory->createAutocompleteField($attributes),
			FieldType::PASSWORD     => $this->fieldsFactory->createPasswordField($attributes),
			FieldType::EMAIL        => $this->fieldsFactory->createEmailField($attributes),
			FieldType::HIDDEN       => $this->fieldsFactory->createHiddenField($attributes),
			FieldType::CSRF         => $this->fieldsFactory->createCsrfTokenField($attributes, $this->session),
			default => throw new FrameworkException('Invalid field type'),
		};
	}

	public function renderField(FieldInterface $field): string
	{
		return $this->fieldsRenderFactory->getRenderer($field);
	}
}