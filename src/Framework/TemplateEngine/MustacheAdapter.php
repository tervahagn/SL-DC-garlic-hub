<?php

namespace App\Framework\TemplateEngine;

class MustacheAdapter extends
	\App\Framework\TemplateEngine\TemplateService implements AdapterInterface
{
	private $mustache;

	public function __construct(\Mustache_Engine $mustache)
	{
		$this->mustache = $mustache;
	}

	public function render(string $template, array $data = []): string
	{
		return $this->mustache->render($template, $data);
	}
}