<?php

namespace App\Framework\Middleware;

use App\Framework\TemplateEngine\TemplateService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Middleware that finalizes the response by rendering the layout or template.
 * It adds execution time and memory usage statistics for non-API routes.
 */
class FinalRenderMiddleware implements MiddlewareInterface
{
	private TemplateService $templateService;

	/**
	 * @param TemplateService $templateService
	 */
	public function __construct(TemplateService $templateService)
	{
		$this->templateService = $templateService;
	}

	/**
	 * @param ServerRequestInterface  $request
	 * @param RequestHandlerInterface $handler
	 *
	 * @return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$response = $handler->handle($request);

		// If it's an API route, return the response without template rendering
		if (str_starts_with($request->getUri()->getPath(), '/api'))
		{
			return $response;
		}

		$layoutData   = $request->getAttribute('layoutData', []);

		if ($_ENV['APP_DEBUG'])
		{
			$start_time   = $request->getAttribute('start_time');
			$start_memory = $request->getAttribute('start_memory');
			$memory_usage = memory_get_usage() - $start_memory;
			$layoutData['EXECUTION_TIME'] = number_format(microtime(true) - $start_time, 6).'sec';
			$layoutData['MEMORY_USAGE'] = round($memory_usage / 1024, 2) . ' KB';
			$layoutData['PEAK_MEMORY_USAGE'] = round(memory_get_peak_usage() / 1024, 2) . ' KB';
		}

		$controllerData = @unserialize((string) $response->getBody());
		if ($controllerData === false)
			return $response->withHeader('Content-Type', 'text/html');

		$mainContent = $this->templateService->render($controllerData['this_layout']['template'], $controllerData['this_layout']['data']);

		$finalContent = $this->templateService->render('layouts/main_layout', array_merge($layoutData,
			['MAIN_CONTENT' => $mainContent], $controllerData['main_layout']));

		$response = $response->withBody(new \Slim\Psr7\Stream(fopen('php://temp', 'r+')));
		$response->getBody()->write($finalContent);

		return $response->withHeader('Content-Type', 'text/html');
	}
}
