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
use App\Framework\Helper\Cookie;
use Exception;

class FormBuilder
{
	private FieldsFactory $fieldsFactory;
	private FieldsRenderFactory $fieldsRenderFactory;
	private Cookie $cookie;

	public function __construct(FieldsFactory $fieldsFactory, FieldsRenderFactory $fieldsRenderFactory, Cookie $cookie)
	{
		$this->fieldsFactory       = $fieldsFactory;
		$this->fieldsRenderFactory = $fieldsRenderFactory;
		$this->cookie              = $cookie;
	}

	/**
	 * @throws FrameworkException
	 * @throws Exception
	 */
	public function createField(array $options = []): FieldInterface
	{
		return match ($options['type']) {
			FieldType::TEXT     => $this->fieldsFactory->createTextField($options),
			FieldType::PASSWORD => $this->fieldsFactory->createPasswordField($options),
			FieldType::EMAIL    => $this->fieldsFactory->createEmailField($options),
			FieldType::CSRF     => $this->fieldsFactory->createCsrfTokenField($options, $this->cookie),
			default => throw new FrameworkException('Invalid field type'),
		};
	}

	public function renderField(FieldInterface $field): string
	{
		return $this->fieldsRenderFactory->getRenderer($field);
	}
}