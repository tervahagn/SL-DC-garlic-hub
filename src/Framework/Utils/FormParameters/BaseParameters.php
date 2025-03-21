<?php
namespace App\Framework\Utils\FormParameters;

use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Exceptions\ModuleException;

/**
 * The BaseParameters class provides functionality to manage parameters configuration,
 * including adding, removing, validating, sanitizing and retrieving parameters and their values.
 * This class is designed to be extended and can include custom hooks for preprocessing and postprocessing
 * of parameter values.
 *
 * Features include:
 * - Adding parameters with detailed type constraints and optional default values.
 * - Removing single or multiple parameters by name.
 * - Retrieving and setting parameter values, including defaults.
 * - Parsing parameter values leveraging sanitization methods.
 * - Detecting and managing user input for parameter processing.
 * - Support for custom hooks before and after parameter parsing.
 *
 * This class includes support for enforcing scalar data types via `ScalarType` and sanitization logic
 * integrated with the `Sanitizer` utility.
 *
 * @throws ModuleException Various methods may throw ModuleException for invalid operations
 *                         such as unsupported scalar types or missing parameters.
 */
abstract class BaseParameters
{
	public const string PARAMETER_UID = 'UID';

	protected readonly string $moduleName;
	protected readonly Sanitizer $sanitizer;
	protected readonly Session $session;
	protected array $currentParameters;
	protected array $userInputs;

	public function __construct(string $moduleName, Sanitizer $sanitizer, Session $session)
	{
		$this->moduleName = $moduleName;
		$this->sanitizer  = $sanitizer;
		$this->session    = $session;
	}

	public function setUserInputs(array $userInputs): static
	{
		$this->userInputs = $userInputs;
		return $this;
	}

	public function addOwner(): void
	{
		$this->addParameter(self::PARAMETER_UID, ScalarType::INT, 0);
	}

	public function addParameter(string $parameter_name, ScalarType $scalarType, mixed $default_value = null): static
	{
		$this->currentParameters[$parameter_name] = ['scalar_type' => $scalarType, 'default_value' => $default_value, 'parsed' => false];
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
	public function removeParameters(array $parameterNames): static
	{
		foreach($parameterNames as $parameter)
		{
			$this->removeParameter($parameter);
		}

		return $this;
	}

	/**
	 * @throws ModuleException
	 */
	public function getValueOfParameter(string $parameter_name): mixed
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
		foreach(array_keys($this->currentParameters) as $parameterName )
		{
			$this->parseInputFilterByName($parameterName);
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
	public function parseInputFilterByName(string|array $parameterName): static
	{
		if (!array_key_exists($parameterName, $this->currentParameters))
			throw new ModuleException($this->moduleName, 'A parameter with name: ' . $parameterName . ' is not found.');

		if (isset($this->userInputs[$parameterName]) &&
				isset($this->currentParameters[$parameterName]['value']) &&
					$this->userInputs[$parameterName] != $this->currentParameters[$parameterName]['value'])
			$this->currentParameters[$parameterName]['parsed'] = false;

		// don't parse them twice
		if ($this->currentParameters[$parameterName]['parsed'] === true)
			return $this;

		if (array_key_exists($parameterName, $this->userInputs))
			$parameterValue = $this->userInputs[$parameterName];
		else
			$parameterValue = $this->currentParameters[$parameterName]['default_value'];

		$parameter = $this->beforeParseHook($parameterName, $this->currentParameters[$parameterName]);

		$value = match ($parameter['scalar_type'])
		{
			ScalarType::INT            => $this->sanitizer->int($parameterValue),
			ScalarType::FLOAT          => $this->sanitizer->float($parameterValue),
			ScalarType::STRING         => $this->sanitizer->string($parameterValue),
			ScalarType::NUMERIC_ARRAY  => $this->sanitizer->intArray($parameterValue),
			ScalarType::STRING_ARRAY   => $this->sanitizer->stringArray($parameterValue),
			ScalarType::HTML_STRING    => $this->sanitizer->html($parameterValue),
			ScalarType::JSON           => $this->sanitizer->jsonArray($parameterValue),
			ScalarType::MEDIAPOOL_FILE => $this->sanitizer->string('hidden_' . $parameterValue),
			ScalarType::BOOLEAN        => $this->sanitizer->bool($parameterValue)
		};

		$parameter['value'] = $value;
		$parameter['parsed'] = true;

		$this->currentParameters[$parameterName] = $this->afterParseHook($parameterName, $parameter);
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