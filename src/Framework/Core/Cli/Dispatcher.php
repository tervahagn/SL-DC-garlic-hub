<?php
namespace App\Framework\Core\Cli;

use App\Framework\Exceptions\CoreException;

/**
 * The Dispatcher class manages the site and CLI base configuration.
 * It is responsible for dispatching API calls by validating and returning
 * the controller file path based on the provided configuration.
 *
 * It ensures that all necessary parameters are set and that the controller
 * file exists for the specified site.
 */
class Dispatcher
{
	protected string $site;

	protected CliBase $CliBase;

	public function __construct()
	{
	}

	/**
	 * @param string $site
	 *
	 * @return 	$this
	 */
	public function setSite(string $site): static
	{
		$this->site = $site;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSite(): string
	{
		return $this->site;
	}

	/**
	 * @return CliBase
	 */
	public function getCliBase(): CliBase
	{
		return $this->CliBase;
	}

	/**
	 * @param CliBase $CliBase
	 * @return $this
	 */
	public function setCliBase(CliBase $CliBase): static
	{
		$this->CliBase = $CliBase;
		return $this;
	}

	/**
	 * @param 	array $main_config
	 * @return 	string
	 * @throws 	CoreException
	 */
	public function dispatchApi(array $main_config): string
	{
		$this->parseSiteParameter()->validateControllerExists($main_config);

		if (!array_key_exists('filepath', $main_config[$this->site]))
			throw new CoreException('Missing filepath for site ' . $this->site);

		return $main_config[$this->site]['filepath'];
	}

	/**
	 * @return Dispatcher
	 * @throws CoreException
	 */
	protected function parseSiteParameter(): static
	{
		$site_parameter = $this->parseSiteParameterFromCli();

		return $this->setSite($site_parameter);
	}

	/**
	 * parses arguments from cli options
	 *
	 * @return string
	 * @throws CoreException
	 */
	protected function parseSiteParameterFromCli(): string
	{
		if (!$this->getCliBase() instanceof CliBase)
		{
			throw new CoreException('Missing CliBase class for CLI');
		}

		$site_parameter = $this->getCliBase()->getSiteParam();

		if (empty($site_parameter))
		{
			throw new CoreException('Missing site parameter for cli call. Use --site or -s as option or --help to show a list of registered site options');
		}

		return $site_parameter;
	}

	/**
	 * @param array $main_config
	 * @return $this
	 * @throws CoreException
	 */
	protected function validateControllerExists(array $main_config): static
	{
		if (empty($this->site))
		{
			throw new CoreException('Site is missing');
		}
		if (!array_key_exists($this->site, $main_config))
		{
			throw new CoreException('Site is invalid. Looking for ' . $this->site);
		}

		return $this;
	}
}
