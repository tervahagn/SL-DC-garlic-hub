<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);

namespace App\Framework\Core\Config;

use App\Framework\Exceptions\CoreException;
use Monolog\Level;

/**
 * The Config class manages application configuration settings.
 *
 * Provides methods to load, cache, and retrieve configuration data for different modules.
 * This class utilizes a ConfigLoaderInterface for flexibility in loading configurations.
 */
class Config
{
	const string PLATFORM_EDITION_EDGE = 'edge';
	const string PLATFORM_EDITION_CORE = 'core';
	const string PLATFORM_EDITION_ENTERPRISE = 'enterprise';

	private ConfigLoaderInterface $configLoader;

	/** @var array<string,mixed> */
	private array $configCache = [];
	/** @var array<string,string> */
	private array $paths;
	/** @var array<string,string>  */
	private array $env;

	/**
	 * @param array<string,string> $paths
	 * @param array<string,string> $env
	 */
	public function __construct(ConfigLoaderInterface $configLoader, array $paths = [], array $env = [])
	{
		$this->configLoader = $configLoader;
		$this->paths        = $paths;
		$this->env          = $env;
	}

	public function getEnv(string $key): string
	{
		return $this->env[$key] ?? '';
	}

	public function getEdition(): string
	{
		$edition = $this->getEnv('APP_PLATFORM_EDITION');
		if (empty($edition))
			return self::PLATFORM_EDITION_EDGE;

		return $edition;
	}

	public function getPaths(string $key): string
	{
		return $this->paths[$key] ?? '';
	}

	public function getLogLevel(): Level
	{
		return match ($this->getEnv('APP_ENV'))
		{
			'dev' => Level::Debug,
			'prod' => Level::Error,
			default => Level::Info,
		};
	}

	/**
	 * Retrieves a specific configuration value.
	 *
	 * Searches for the value in the given module and optional section.
	 *
	 * @param string      $key     The configuration key to retrieve.
	 * @param string      $module  The name of the module.
	 * @param string|null $section Optional. The section within the module.
	 *
	 * @return mixed|null The configuration value or null if not found.
	 * @throws CoreException
	 */
	public function getConfigValue(string $key, string $module, ?string $section = null): mixed
	{
		$config = $this->getConfigForModule($module);

		if ($section !== null && array_key_exists($section, $config))
			$config = $config[$section];


		return $config[$key] ?? null;
	}

	/**
	 * @return array<string,mixed>
	 * @throws CoreException
	 */
	public function getFullConfigDataByModule(string $module): array
	{
		return $this->getConfigForModule($module);
	}

	/**
	 * @return array<string,mixed>
	 * @throws CoreException
	 */
	private function getConfigForModule(string $module): array
	{
		if (!isset($this->configCache[$module])) {
			$this->configCache[$module] = $this->configLoader->load($module);
		}

		return $this->configCache[$module];
	}

	/**
	 * @param string[] $modules An array of module names to preload.
	 * @throws CoreException
	 */
	public function preloadModules(array $modules): void
	{
		foreach ($modules as $module) {
			$this->getConfigForModule($module);
		}
	}
}
