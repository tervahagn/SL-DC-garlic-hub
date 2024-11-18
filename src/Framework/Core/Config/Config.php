<?php

namespace App\Framework\Core\Config;

/**
 * The Config class manages application configuration settings.
 *
 * Provides methods to load, cache, and retrieve configuration data for different modules.
 * This class utilizes a ConfigLoaderInterface for flexibility in loading configurations.
 */
class Config
{
	/**
	 * @var ConfigLoaderInterface Handles the loading of configuration files.
	 */
	private ConfigLoaderInterface $configLoader;

	/**
	 * @var array Caches loaded configuration data to reduce redundant loads.
	 */
	private array $configCache = [];

	/**
	 * Initializes the Config class with a configuration loader.
	 *
	 * @param ConfigLoaderInterface $configLoader The loader responsible for fetching configuration data.
	 */
	public function __construct(ConfigLoaderInterface $configLoader)
	{
		$this->configLoader = $configLoader;
	}

	/**
	 * Retrieves a specific configuration value.
	 *
	 * Searches for the value in the given module and optional section.
	 *
	 * @param string      $key The configuration key to retrieve.
	 * @param string      $module The name of the module.
	 * @param string|null $section Optional. The section within the module.
	 * @return mixed|null The configuration value or null if not found.
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
	 * @return array The full configuration data for the module.
	 */
	public function getFullConfigDataByModule(string $module): array
	{
		return $this->getConfigForModule($module);
	}

	/**
	 * Loads configuration data for a module, caching it for future use.
	 *
	 * @param string $module The name of the module.
	 * @return array The cached configuration data for the module.
	 */
	private function getConfigForModule(string $module): array
	{
		if (!isset($this->configCache[$module])) {
			$this->configCache[$module] = $this->configLoader->loadConfig($module);
		}

		return $this->configCache[$module];
	}

	/**
	 * Preloads configuration data for multiple modules.
	 *
	 * This method ensures the configuration for the specified modules is cached.
	 *
	 * @param array $modules An array of module names to preload.
	 * @return void
	 */
	public function preloadModules(array $modules): void
	{
		foreach ($modules as $module) {
			$this->getConfigForModule($module);
		}
	}
}
