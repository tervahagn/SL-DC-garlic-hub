<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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


namespace App\Framework\Core;

use App\Framework\Exceptions\CoreException;

class SystemStats
{
	private ShellExecutor $shellExecutor;

	private array $ramStats = [];
	private array $discInfo = [];
	private array $loadData = [];
	private bool $isLinux = false;

	public function isLinux(): bool
	{
		return $this->isLinux;
	}

	public function getDiscInfo(): array
	{
		return $this->discInfo;
	}

	public function getRamStats(): array
	{
		return $this->ramStats;
	}

	public function getLoadData(): array
	{
		return $this->loadData;
	}

	public function __construct(ShellExecutor $shellExecutor)
	{
		$this->shellExecutor = $shellExecutor;

		if (strcasecmp(PHP_OS, 'Linux') == 0)
			$this->isLinux = true;
	}

	/**
	 * @throws CoreException
	 */
	public function determineSystemStats(): void
	{
		if (!$this->isLinux)
			return;

		$this->determineRamStats();
		$this->determineDiskUsage();
		$this->determineSystemLoad();
	}

	/**
	 * @throws CoreException
	 */
	public function determineRamStats(): void
	{
		$free = $this->shellExecutor->setCommand('free -m')->executeSimple();
		$freeArr = explode("\n", $free);
		$mem = explode(" ", $freeArr[1]);
		$mem = array_filter($mem); // Remove empty values
		$mem = array_merge($mem); // Reindex array

		$this->ramStats = [
			'total' => $mem[1],
			'used'  => $mem[2],
			'free'  => $mem[3],
		];
	}

	public function determineSystemLoad(): void
	{
		$load = sys_getloadavg();
		$this->loadData =  [
			'1_min' => number_format($load[0], 2, '.', ''),
			'5_min' => number_format($load[1], 2, '.', ''),
			'15_min' => number_format($load[2], 2, '.', '')
		];
	}

	/**
	 * @throws CoreException
	 */
	public function determineDiskUsage(): void
	{
		$output = $this->shellExecutor->setCommand('df -h --total')->executeSimple();

		$lines = explode("\n", $output);
		foreach ($lines as $line)
		{
			$line = trim($line);

			if (str_starts_with($line, 'total'))
			{
				$parts = preg_split('/\s+/', $line);
				if (count($parts) >= 6)
				{
					$this->discInfo = [
						'size' => $parts[1],
						'used' => $parts[2],
						'available' => $parts[3],
						'percent' => $parts[4]
					];
				}
			}
		}
	}

}