<?php
namespace App\Framework\Utils\FormParameters;

use App\Framework\Core\Sanitizer;
use App\Framework\Exceptions\ModuleException;

abstract class BaseParameters
{

	protected readonly string $moduleName;
	protected readonly Sanitizer $sanitizer;
	protected array $currentParameters;

	public function __construct(string $moduleName, Sanitizer $sanitizer)
	{
		$this->moduleName        = $moduleName;
		$this->sanitizer         = $sanitizer;
	}

	/**
	 *@throws ModuleException
	 */
	public function addParameter(string $parameter_name, ScalarType $scalarType, mixed $default_value = null): static
	{
		if ($scalarType != ScalarType::INT &&
			$scalarType != ScalarType::FLOAT &&
			$scalarType != ScalarType::BOOLEAN &&
			$scalarType !== ScalarType::STRING &&
			$scalarType !== ScalarType::NUMERIC_ARRAY  &&
			$scalarType !== ScalarType::STRING_ARRAY  &&
			$scalarType !== ScalarType::HTML_STRING &&
			$scalarType !== ScalarType::JSON &&
			$scalarType !== ScalarType::JSON_HTML &&
			$scalarType !== ScalarType::MEDIAPOOL_FILE &&
			$scalarType !== ScalarType::BOOLEAN)
		{
			throw new ModuleException($this->moduleName, 'Unsupported scalar type: ' . $scalarType->value);
		}

		$this->currentParameters[$parameter_name] = array('scalar_type' => $scalarType, 'default_value' => $default_value, 'parsed' => false);
		return $this;
	}

	/**
	 * @param string $parameter_name
	 * @return  $this
	 */
	public function removeParameter(string $parameter_name): static
	{
		if (array_key_exists($parameter_name, $this->currentParameters))
		{
			unset ($this->currentParameters[$parameter_name]);
		}

		return $this;
	}

	/**
	 * method to remove multiple parameters at once
	 */
	public function removeParameters(array $parameter_names): static
	{
		foreach($parameter_names as $parameter)
		{
			$this->removeParameter($parameter);
		}

		return $this;
	}

	/**
	 * @throws ModuleException
	 */
	public function getValueOfParameter(string $parameter_name): static
	{
		if (!$this->hasParameter($parameter_name))
			throw new ModuleException($this->moduleName, 'A parameter with name: ' . $parameter_name . ' is not found.');

		if (!array_key_exists('value', $this->currentParameters[$parameter_name]))
			throw new ModuleException($this->moduleName, 'A value for parameter with name: ' . $parameter_name . ' is not set.');

		return $this->currentParameters[$parameter_name]['value'];
	}

	/**
	 * @throws ModuleException
	 */
	public function setValueOfParameter($parameter_name, $value): static
	{
		if (!$this->hasParameter($parameter_name))
			throw new ModuleException($this->moduleName, 'A parameter with name: ' . $parameter_name . ' is not found.');

		$this->currentParameters[$parameter_name]['value'] = $value;
		return $this;
	}

	/**
	 * @throws ModuleException
	 */
	public function setDefaultForParameter($parameter_name, $default_value): static
	{
		if (!$this->hasParameter($parameter_name))
			throw new ModuleException($this->moduleName, 'A parameter with name: ' . $parameter_name . ' is not found.');

		$this->currentParameters[$parameter_name]['default_value'] = $default_value;
		return $this;
	}


	public function hasParameter($parameter_name): bool
	{
		return (array_key_exists($parameter_name, $this->currentParameters));
	}

	public function getInputParametersArray(): array
	{
		return $this->currentParameters;
	}

	public function getInputValuesArray(): array
	{
		return array_column($this->currentParameters, 'value');
	}

	public function getInputParametersKeys(): array
	{
		return array_keys($this->currentParameters);
	}


	/**
	 * iterates over parameters and parse them
	 * can be used by async calls directly, without using the session store
	 *
	 * if you want to use the session store, use method above
	 * @see BaseFilterParameters::parseInputFilterAllUsers()
	 *
	 * @throws ModuleException
	 */
	public function parseInputAllParameters(): static
	{
		foreach(array_keys($this->currentParameters) as $parameter_name )
		{
			$this->parseInputFilterByName($parameter_name);
		}
		return $this;
	}

	/**
	 * @throws ModuleException
	 */
	public function getDefaultValueOfParameter(string $parameter_name): mixed
	{
		if (!array_key_exists($parameter_name, $this->currentParameters))
		{
			throw new ModuleException($this->moduleName, 'A parameter with name: ' . $parameter_name . ' is not found.');
		}
		return $this->currentParameters[$parameter_name]['default_value'];
	}

	/**
	 * @throws  ModuleException
	 */
	public function parseInputFilterByName(string|array $parameter_name): static
	{
		if (!array_key_exists($parameter_name, $this->currentParameters))
			throw new ModuleException($this->moduleName, 'A parameter with name: ' . $parameter_name . ' is not found.');

		// don't parse them twice
		if ($this->currentParameters[$parameter_name]['parsed'] === true)
			return $this;

		$parameter = $this->beforeParseHook($parameter_name, $this->currentParameters[$parameter_name]);
		$value = match ($parameter['scalar_type'])
		{
			ScalarType::INT            => $this->sanitizer->int($parameter_name, (int)$parameter['default_value']),
			ScalarType::FLOAT          => $this->sanitizer->float($parameter_name, $parameter['default_value']),
			ScalarType::STRING         => $this->sanitizer->string($parameter_name, $parameter['default_value']),
			ScalarType::NUMERIC_ARRAY  => $this->sanitizer->intArray($parameter_name),
			ScalarType::STRING_ARRAY   => $this->sanitizer->stringArray($parameter_name),
			ScalarType::HTML_STRING    => $this->sanitizer->html($parameter_name, $parameter['default_value']),
			ScalarType::JSON           => $this->sanitizer->jsonArray($parameter_name, $parameter['default_value']),
			ScalarType::MEDIAPOOL_FILE => $this->sanitizer->string('hidden_' . $parameter_name, $parameter['default_value']),
			ScalarType::BOOLEAN        => $this->sanitizer->bool($parameter_name, $parameter['default_value']),
			default => throw new ModuleException($this->moduleName, 'Unknown scalar type: ' . $parameter['scalar_type']),
		};

		$parameter['value'] = $value;
		$parameter['parsed'] = true;

		$this->currentParameters[$parameter_name] = $this->afterParseHook($parameter_name, $parameter);
		return $this;
	}

	/**
	 * Hook that can be overwritten in your class.
	 * Will be called after parsing
	 */
	protected function afterParseHook($parameter_name, $parameter): array
	{
		return $parameter;
	}

	/**
	 * Hook that can be overwritten in your class
	 * Will be called before parsing, but if the parameter has not already been parsed
	 **/
	protected function beforeParseHook($parameter_name, $parameter): array
	{
		return $parameter;
	}
}