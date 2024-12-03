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

namespace App\Framework\Core\Config;

use App\Framework\Exceptions\CoreException;
use Monolog\Level;

/**
 * The Config class manages application configuration settings.
 *
 * Provides methods to load, cache, and retrieve configuration data for different modules.
 * This class utilizes a ConfigLoaderInterface for flexibility in loading configurations.
 */
final class Config
{
	const PLATFORM_EDITION_EDGE = 'edge';
	const PLATFORM_EDITION_CORE = 'core';
	const PLATFORM_EDITION_ENTERPRISE = 'enterprise';

	/**
	 * @var ConfigLoaderInterface Handles the loading of configuration files.
	 */
	private ConfigLoaderInterface $configLoader;

	/**
	 * @var array Caches loaded configuration data to reduce redundant loads.
	 */
	private array $configCache = [];
	private array $paths;
	private array $env;

	/**
	 * Initializes the Config class with a configuration loader.
	 *
	 * @param ConfigLoaderInterface $configLoader The loader responsible for fetching configuration data.
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
		return $this->getEnv('APP_PLATFORM_EDITION') ?? self::PLATFORM_EDITION_EDGE;
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
			'test' => Level::Info,
			'prod' => Level::Error,
			default => Level::Warning,
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

		if ($section !== null && array_key_exists($section, $config)) {
			$config = $config[$section];
		}

		return $config[$key] ?? null;
	}

	/**
	 * Retrieves all configuration data for a specific module.
	 *
	 * @param string $module The name of the module.
	 *
	 * @return array The full configuration data for the module.
	 * @throws CoreException
	 */
	public function getFullConfigDataByModule(string $module): array
	{
		return $this->getConfigForModule($module);
	}

	/**
	 * Loads configuration data for a module, caching it for future use.
	 *
	 * @param string $module The name of the module.
	 *
	 * @return array The cached configuration data for the module.
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
	 * Preloads configuration data for multiple modules.
	 *
	 * This method ensures the configuration for the specified modules is cached.
	 *
	 * @param array $modules An array of module names to preload.
	 *
	 * @return void
	 * @throws CoreException
	 */
	public function preloadModules(array $modules): void
	{
		foreach ($modules as $module) {
			$this->getConfigForModule($module);
		}
	}
}
