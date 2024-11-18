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

namespace App\Framework\Core\Cli;
/**
 * Note:
 * when adding new params, you need to adjust the method self::parseBaseParams()
 * and add your new param there.
 */
class CliBase
{
	/**
	 * @var bool
	 */
	protected bool $verbose_flag = false;

	/**
	 * @var bool
	 */
	protected bool $show_help  = false;

	/**
	 * @var string
	 */
	protected string $site_param = '';

	/**
	 * @var array
	 */
	protected array $additional_parameters = array();

	/**
	 * @var array
	 */
	protected array $controller_help_text = array();

	public function __construct() { }

	/**
	 * parses the basic parameters of cli call (site parameter, verbose flag and help_flag)
	 *
	 * @return $this
	 */
	public function parseBaseParams(): static
	{
		$short_opts = "s:i:p:c:m:d:n:vh";
		$long_opts	= array('site:', 'verbose', 'help', 'id:', 'channel:', 'player:', 'module:', 'direction', 'number', 'source:', 'target:');

		// for convenience: to iterate over all additional options
		// the key is the option we are checking for
		// the value is the key for ::$additional_parameters[] array
		$iterator = array(
			'i'         => 'id',
			'id'        => 'id',
			'p'         => 'player',
			'player'    => 'player',
			'c'         => 'channel',
			'channel'   => 'channel',
			'm'         => 'module',
			'module'    => 'module',
			'd'         => 'direction',
			'direction' => 'direction',
			'n'         => 'number',
			'number'    => 'number',
			'src'       => 'source',
			'source'    => 'source',
			'trg'       => 'target',
			'target'    => 'target'
		);

		$base_options = getopt($short_opts, $long_opts);

		$this->verbose_flag = (array_key_exists('v', $base_options) || array_key_exists('verbose', $base_options));
		$this->show_help    = (array_key_exists('h', $base_options) || array_key_exists('help', $base_options));

		$site = '';

		if (array_key_exists('s', $base_options))
		{
			$site = trim($base_options['s']);
		}
		elseif (array_key_exists('site', $base_options))
		{
			$site = trim($base_options['site']);
		}

		// parse all possible additional options
		foreach($iterator as $option => $key)
		{
			if (array_key_exists($option, $base_options))
			{
				$this->additional_parameters[$key] = trim($base_options[$option]);
			}
		}

		$this->site_param = $site;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSiteParam(): string
	{
		return $this->site_param;
	}

	/**
	 * @return bool
	 */
	public function hasSiteParam(): bool
	{
		return (!empty($this->site_param));
	}

	/**
	 * @return bool
	 */
	public function isHelp(): bool
	{
		return $this->show_help;
	}

	/**
	 * @return bool
	 */
	public function isVerbose(): bool
	{
		return $this->verbose_flag;
	}

	/**
	 * @return array
	 */
	public function getAdditionalParameters(): array
	{
		return $this->additional_parameters;
	}

	/**
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function getAdditionalParameterByKey(string $key): mixed
	{
		if ($this->hasAdditionalParameterByKey($key))
		{
			return $this->additional_parameters[$key];
		}

		return null;
	}

	/**
	 * @param string $key
	 *
	 * @return  bool
	 */
	public function hasAdditionalParameterByKey(string $key): bool
	{
		return array_key_exists($key, $this->additional_parameters);
	}

	/**
	 * @param string $description
	 * @param string $usage
	 * @param array  $options
	 * @param bool   $append_default_options
	 *
	 * @return  $this
	 */
	public function registerControllerHelpText(string $description, string $usage, array $options = array(), bool $append_default_options = true): static
	{
		if ($append_default_options === true)
		{
			$options = array_merge($options, $this->returnStandardControllerOptionsHelp());
		}

		// replace any "|| " with "or" + \n
		$usage = str_replace('|| ', 'or' . PHP_EOL, $usage);

		$this->controller_help_text = array(
			'description'   => $description,
			'usage'         => CliColors::colorizeString($usage, CliColors::CLI_COLOR_YELLOW),
			'options'       => $this->buildOptionsHelpText($options)
		);
		return $this;
	}

	public function showCliError(string $error_message = ''): void
	{
		print PHP_EOL;
		print CliColors::colorizeString($error_message, CliColors::CLI_COLOR_RED);
		print PHP_EOL;
	}

	public function showCliInfo(string $info_message = ''): void
	{
		print PHP_EOL;
		print CliColors::colorizeString($info_message, CliColors::CLI_COLOR_GREEN);
		print PHP_EOL;
	}

	public function showCliWarning(string $warning_message = ''): void
	{
		print PHP_EOL;
		print CliColors::colorizeString($warning_message, CliColors::CLI_COLOR_YELLOW);
		print PHP_EOL;
	}


	/**
	 * @param string $error_message
	 *
	 * @return $this
	 */
	public function showCliControllerHelp(string $error_message = ''): static
	{
		if (empty($this->controller_help_text))
		{
			print PHP_EOL;
			print "No help text for controller " . CliColors::colorizeString($this->site_param, CliColors::CLI_COLOR_BLUE) . " available";
			print PHP_EOL;
			return $this;
		}

		print PHP_EOL;

		foreach($this->controller_help_text as $key => $line)
		{
			if ($key == 'description' && !empty($error_message))
			{
				$line = $error_message;
			}
			print $line . PHP_EOL . PHP_EOL;
		}

		return $this;
	}

	/**
	 * @param array $main_config
	 * @return $this
	 */
	public function showCliHelp(array $main_config): static
	{
		// first collect all possible controllers
		$sites_available = array();

		foreach($main_config as $key => $controller)
		{
			$file_exists = file_exists($controller['filepath']);
			$sites_available[] = ($file_exists) ? CliColors::colorizeString($key."\t".$controller['description'], CliColors::CLI_COLOR_GREEN) : CliColors::colorizeString($key, CliColors::CLI_COLOR_RED);
		}

		$available_sites = implode("\n", $sites_available);

		// now prepare the options help text
		$options = array(
			'-s [SITE], --site [SITE]'      => 'Controller name (site)',
			'-h, --help'                    => 'Show help. Without any Site given, this help message will be displayed',
			'-v, --verbose'                 => 'Pass the verbose parameter to the controller, which should print out more informative details',
		);

		$options_help = $this->buildOptionsHelpText($options);

		// now all together...
        $text = <<<text

CLI controller

Possible options:

{$options_help}

List of possibles Sites:

{$available_sites}

text;
		print $text . PHP_EOL;
		return $this;
	}

	/**
	 * @return string[]
	 */
	protected function returnStandardControllerOptionsHelp(): array
	{
		return array(
			'-h, --help'        => 'Show this help message',
			'-v, --verbose'     => 'Pass the verbose parameter to the controller, which should print out more informative details'
		);
	}

	/**
	 * @param array $options_array
	 * @return string
	 */
	protected function buildOptionsHelpText(array $options_array): string
	{
		$text = '';

		foreach($options_array as $possible_options => $description)
		{
			$text .= str_pad($possible_options, 30) . $description . PHP_EOL;
		}
		return $text;
	}

}