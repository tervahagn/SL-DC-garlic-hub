<?php

namespace App\Framework\Twig;

use App\Framework\Services\PerformanceMonitor;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PerformanceExtension extends AbstractExtension
{
	private PerformanceMonitor $performanceMonitor;

	public function __construct(PerformanceMonitor $performanceMonitor)
	{
		$this->performanceMonitor = $performanceMonitor;
	}

	public function getFunctions(): array
	{
		return [
			new TwigFunction('execution_time', [$this->performanceMonitor, 'getExecutionTime']),
			new TwigFunction('memory_usage', [$this->performanceMonitor, 'getMemoryUsage']),
			new TwigFunction('peak_memory_usage', [$this->performanceMonitor, 'getPeakMemoryUsage']),
		];
	}
}