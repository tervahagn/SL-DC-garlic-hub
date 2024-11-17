<?php

namespace App\Framework\TemplateEngine;

class TemplateService
{
	private AdapterInterface $templateEngine;

	public function __construct(AdapterInterface $templateEngine)
	{
		$this->templateEngine = $templateEngine;
	}

	public function render(string $template, array $data = []): string
	{
		return $this->templateEngine->render($template, $data);
	}
}