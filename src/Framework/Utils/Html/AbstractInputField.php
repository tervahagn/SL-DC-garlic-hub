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
	private string $label;
	private string $id;
	private string $title;
	private FieldType $type;
	private string $value;
	private string $defaultValue;
	/** @var array<string,mixed>  */
	private array $attributes;
	/** @var array<string,mixed>  */
	private array $validationRules;

	/**
	 * @param array<string,mixed> $attributes
	 */
	public function __construct(array $attributes = [])
	{
		$this->id              = $attributes['id'];
		$this->type            = $attributes['type'];
		$this->name            = $attributes['name'] ?? $attributes['id'];
		$this->title           = $attributes['title'] ?? '';
		$this->label           = $attributes['label'] ?? '';
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

	/**
	 * @param array<string,mixed> $rules
	 */
	public function setValidationRules(array $rules): static
	{
		$this->validationRules = $rules;
		return $this;
	}

	public function setAttribute(string $name, string $value): static
	{
		$this->attributes[$name] = $value;
		return $this;
	}

	public function addValidationRule(string $rule, bool $value = true): static
	{
		$this->validationRules[$rule] = $value;
		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getType(): FieldType
	{
		return $this->type;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getLabel(): string
	{
		return $this->label;
	}

	public function getValue(): ?string
	{
		if (empty($this->value))
			return $this->defaultValue;

		return $this->value;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getAttributes(): array
	{
		return $this->attributes;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getValidationRules(): array
	{
		return $this->validationRules;
	}

}