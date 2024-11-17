<?php
namespace App\Framework\Core;

use App\Framework\Exceptions\CoreException;

/**
 * Class Config
 */
class Config
{
	private array $config_stack = array();
	private string $config_path;

	/**
	 * @param string|null $config_path
	 */
	public function __construct(?string $config_path = null)
	{
		$this->config_path = $config_path ?? __DIR__ . '/../../../config/';
	}

	/**
	 * @param string      $key
	 * @param string      $module
	 * @param string|null $section
	 *
	 * @return mixed|null
	 * @throws CoreException
	 */
	public function getConfigValue(string $key,	string $module, string $section = null): mixed
	{
		if (!array_key_exists($module, $this->config_stack))
		{
			$this->loadConfig($module);
		}

		if (!is_null($section))
		{
			$ar_config = $this->getConfigSection($module, $section);
		}
		else
		{
			$ar_config = $this->config_stack[$module];
		}

		if (array_key_exists($key, $ar_config))
		{
			return $ar_config[$key];
		}

		return null;
	}

	/**
	 * returns the full config data of a config file as an array
	 *
	 * @param string $module
	 *
	 * @return array|null
	 * @throws CoreException
	 */
	public function getFullConfigDataByModule(string $module): ?array
	{
		if (!array_key_exists($module, $this->config_stack))
		{
			$this->loadConfig($module);
		}

		if (isset($this->config_stack[$module]))
		{
			return $this->config_stack[$module];
		}

		return null;
	}

	/**
	 * @param array $ar_modules
	 * @return $this
	 * @throws CoreException
	 */
	public function preLoadConfigs(array $ar_modules): static
	{
		foreach($ar_modules as $module)
		{
			$this->loadConfig($module);
		}

		return $this;
	}

	/**
	 * @param string $path
	 *
	 * @return $this
	 */
	public function setConfigPath(string $path): static
	{
		$this->config_path = $path;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getConfigPath(): string
	{
		return $this->config_path;
	}

	/**
	 * @param string $module
	 *
	 * @return  $this
	 * @throws CoreException
	 */
	private function loadConfig(string $module): static
	{
		$file_name = $this->buildConfigFileName($module);

		if (!file_exists($file_name) || !is_readable($file_name))
			throw new CoreException('Cannot load or read configuration file: ' . $file_name);

		$ar_config_values = parse_ini_file($file_name, true);

		if ($ar_config_values === false)
			throw new CoreException('Syntax error in configuration file: ' . $file_name);

		$this->config_stack[$module] = $ar_config_values;
		return $this;
	}

	/**
	 * @param $module
	 * @return string
	 */
	private function buildConfigFileName($module): string
	{
		return sprintf('%sconfig_%s.ini', $this->getConfigPath(), strtolower($module));
	}

	/**
	 * @param string $module_name
	 * @param string $section
	 *
	 * @return array
	 * @throws CoreException
	 */
	private function getConfigSection(string $module_name, string $section): array
	{
		if (!array_key_exists($module_name, $this->config_stack))
			throw new CoreException("Module '$module_name' is not loaded.");

		if (!array_key_exists($section, $this->config_stack[$module_name]))
			throw new CoreException("Section '$section' does not exist in module '$module_name'.");

		return $this->config_stack[$module_name][$section];
	}
}