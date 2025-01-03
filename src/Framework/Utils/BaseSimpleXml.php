<?php
namespace App\Framework\Utils;

use App\Framework\Exceptions\ModuleException;
use LibXMLError;
use SimpleXMLElement;

/**
 * This is just a little helper for using SimpleXMl in php
 * In fact it just provides a wrapper for the php function
 * to deal with libxml errors.
 *
 * It throws an exception if simplexml_load_string() (or maybe simplexml_load_file())
 * fails due to XMl parsing errors
 * Additionally it provides a public method to retrieve the libxml error
 *
 * So, if your class is using PHP's SimpleXML class, just extend from this, to use the two methods.
 *
 * It needs an abstract method getModuleName() (protected)
 * we need this to throw correct exceptions
 *
 */
abstract class BaseSimpleXml
{
	protected array $xml_errors;

	protected SimpleXMLElement $xml_obj;

	abstract protected function getModuleName(): string;

	public function getXmlObj(): SimpleXMLElement
	{
		return $this->xml_obj;
	}

	public function setXmlObj(SimpleXMLElement $xml_obj): static
	{
		$this->xml_obj = $xml_obj;
		return $this;
	}

	protected function getXmlErrors(): array
	{
		return $this->xml_errors;
	}

	/**
	 * @return $this
	 */
	protected function setXmlErrors(array $xml_errors): static
	{
		$this->xml_errors = $xml_errors;
		return $this;
	}

	/**
	 * @throws  ModuleException
	 */
	protected function loadXmlFromString(string $xml_input): static
	{
		libxml_use_internal_errors(true);

		// clear possible previously stored errors
		$this->xml_errors = array();
		libxml_clear_errors();

		$simple_xml = simplexml_load_string($xml_input);

		if ($simple_xml === false)
		{
			throw new ModuleException($this->getModuleName(), 'Error reading/parsing xml');
		}
		$this->setXmlObj($simple_xml);
		return $this;
	}

	/**
	 * @throws  ModuleException
	 */
	protected function loadXMlFromFile(string $file_name): static
	{
		libxml_use_internal_errors(true);

		// clear possible previously stored errors
		$this->xml_errors = array();
		libxml_clear_errors();

		$simple_xml = simplexml_load_file($file_name);

		if ($simple_xml === false)
			throw new ModuleException($this->getModuleName(), 'Error reading/parsing xml');

		$this->setXmlObj($simple_xml);
		return $this;
	}

	public function getXmlErrorArray(): array
	{
		$this->buildXmlErrors();
		return $this->getXmlErrors();
	}


	public function getXmlErrorsAsString(): ?string
	{
		$this->buildXmlErrors();

		if (array_key_exists(0, $this->xml_errors))
		{
			$last_error = $this->xml_errors[0];
			/* @var $last_error LibXMLError */

			return sprintf('%s: %s Line: %s, Column: %s',
				$this->getNamedErrorLevel($last_error->level),
				trim($last_error->message),
				$last_error->line,
				$last_error->column);
		}

		return null;
	}

	/**
	 * reads the internal libxml_error api and returns
	 * the errors as a string (concatenated by line breaks)
	 *
	 * since this clears the libxml error, it should not
	 * called from outside
	 *
	 */
	private function buildXmlErrors(): void
	{
		$ar_error_lines = array();

		foreach(libxml_get_errors() as $error)
		{
			$ar_error_lines[] = $error;
		}

		$this->setXmlErrors($ar_error_lines);
	}

	private function getNamedErrorLevel(int $error_level): string
	{
		return match ($error_level)
		{
			LIBXML_ERR_WARNING => 'Warning',
			LIBXML_ERR_ERROR => 'Recoverable Error',
			LIBXML_ERR_FATAL => 'Fatal Error',
			default => 'Unknown level',
		};
	}
}