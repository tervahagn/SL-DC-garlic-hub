<?php
namespace App\Framework\Utils;

/**
 * Just a simple storage container for a table header field
*/
class HeaderField
{
	protected string $name = '';
	protected bool $sortable = false;

	protected bool $skipTranslation = false;
	protected string $specificLangModule;

	public function isSortable(): bool
	{
		return $this->sortable;
	}

	public function sortable(bool $sortable): static
	{
		$this->sortable = $sortable;
		return $this;
	}

	public function skipTranslation(bool $translated): static
	{
		$this->skipTranslation = $translated;
		return $this;
	}

	public function shouldSkipTranslation(): bool
	{
		return $this->skipTranslation;
	}

	public function useSpecificLangModule(array $lang_module): static
	{
		$this->specificLangModule = $lang_module;
		return $this;
	}

	public function hasSpecificLangModule(): bool
	{
		return !empty($this->specificLangModule);
	}

	public function getSpecificLanguageModule(): string
	{
		return $this->specificLangModule;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): static
	{
		$this->name = $name;
		return $this;
	}
}
