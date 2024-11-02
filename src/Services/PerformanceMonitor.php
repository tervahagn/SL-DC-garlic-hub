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

namespace App\Services;

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