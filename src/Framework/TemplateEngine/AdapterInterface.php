<?php

namespace App\Framework\TemplateEngine;

interface AdapterInterface
{
	public function render(string $template, array $data = []): string;

}