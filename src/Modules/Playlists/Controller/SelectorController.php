<?php

namespace App\Modules\Playlists\Controller;

use App\Framework\Core\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SelectorController
{
	private Config $config;

	/**
	 * @param Config $config
	 */
	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public function loadTemplate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$filePath = $this->config->getPaths('templateDir').'/playlists/selector.html';
		$template = file_get_contents($filePath);

		$data = ['success' => true, 'template' => $template];

		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}

}