<?php

namespace App\Framework\Services;

class PerformanceMonitor
{
	private float $startTime;

	public function __construct()
	{
		$this->startTime = microtime(true);
	}

	public function getExecutionTime(): float
	{
		return microtime(true) - $this->startTime;
	}

	public function getMemoryUsage(): string
	{
		return number_format(memory_get_usage() / 1024 / 1024, 2) . ' MB';
	}

	public function getPeakMemoryUsage(): string
	{
		return number_format(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB';
	}
}