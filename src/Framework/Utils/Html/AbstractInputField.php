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

class AbstractInputField implements FieldInterface
{
	private string $name;
	private string $translatedName;
	private string $id;
	private ?string $value;
	private ?string $defaultValue;
	private array $attributes;
	private array $validationRules;

	public function __construct(array $attributes = [])
	{
		$this->id              = $attributes['id'];
		$this->name            = $attributes['name'] ?? $attributes['id'];
		$this->translatedName  = $attributes['translated_name'] ?? '';
		$this->value           = $attributes['value'] ?? '';
		$this->defaultValue    = $attributes['default_value'] ?? '';
		$this->validationRules = $attributes['rules'] ?? [];
		$this->attributes      = $attributes['attributes'] ?? [];
	}

	public function setValue(string $value): self
	{
		$this->value = $value;
		return $this;
	}

	public function setValidationRules(array $rules): static
	{
		$this->validationRules = $rules;
		return $this;
	}

	public function setAttribute(string $name, string $value): self
	{
		$this->attributes[$name] = $value;
		return $this;
	}

	public function addValidationRule(string $rule, $value = true): self
	{
		$this->validationRules[$rule] = $value;
		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getTranslatedName(): string
	{
		return $this->translatedName;
	}

	public function getValue(): ?string
	{
		if (empty($this->value))
			return $this->defaultValue;

		return $this->value;
	}

	public function getAttributes(): array
	{
		return $this->attributes;
	}

	public function getValidationRules(): array
	{
		return $this->validationRules;
	}

}