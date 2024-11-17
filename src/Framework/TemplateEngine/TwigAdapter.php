<?php

namespace App\Framework\TemplateEngine;

class TwigAdapter implements AdapterInterface
{
	private $twig;

	public function __construct(\Twig\Environmen $twig)
	{
		$this->twig = $twig;
	}

	public function render(string $template, array $data = []): string
	{
		return $this->twig->render($template, $data);
	}
}