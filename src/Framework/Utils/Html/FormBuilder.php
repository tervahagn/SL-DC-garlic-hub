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

use App\Framework\Exceptions\FrameworkException;

class FormBuilder
{
	private FieldsFactory $fieldsFactory;
	private FieldsRenderFactory $fieldsRenderFactory;

	public function __construct(FieldsFactory $fieldsFactory, FieldsRenderFactory $fieldsRenderFactory)
	{
		$this->fieldsFactory = $fieldsFactory;
		$this->fieldsRenderFactory = $fieldsRenderFactory;
	}

	/**
	 * @throws FrameworkException
	 */
	public function createField(array $options = []): FieldInterface
	{
		switch($options['type'])
		{
			case FieldType::TEXT:
				return $this->fieldsFactory->createTextField($options);
			case FieldType::PASSWORD:
				return $this->fieldsFactory->createPasswordField($options);
			case FieldType::EMAIL:
				return $this->fieldsFactory->createEmailField($options);
			case FieldType::CSRF:
				return $this->fieldsFactory->createCsrfTokenField($options);
			default:
				throw new FrameworkException('Invalid field type');
		}
	}

	public function renderField(FieldInterface $field): string
	{
		return $this->fieldsRenderFactory->getRenderer($field);
	}
}