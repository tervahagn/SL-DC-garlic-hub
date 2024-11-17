<?php
namespace App\Framework\Core\Cli;

class CliColors
{
	const CLI_COLOR_DEFAULT = '0;39';
	const CLI_COLOR_RED		= '0;31';
	const CLI_COLOR_BLUE	= '1;34';
	const CLI_COLOR_YELLOW	= '1;33';
	const CLI_COLOR_GREEN	= '0;32';
	const CLI_COLOR_BOLD	= '1';
	const CLI_COLOR_DIM		= '2';

	/**
	 * @var array
	 */
	static array $foreground_colors = array(
		self::CLI_COLOR_DEFAULT,
		self::CLI_COLOR_BLUE,
		self::CLI_COLOR_BOLD,
		self::CLI_COLOR_DIM,
		self::CLI_COLOR_GREEN,
		self::CLI_COLOR_RED,
		self::CLI_COLOR_YELLOW
	);

	/**
	 * formats a command line string with color
	 *
	 * @param string $text
	 * @param string $color
	 *
	 * @return 	string
	 */
	public static function colorizeString(string $text, string $color = self::CLI_COLOR_DEFAULT): string
	{
		if (!in_array($color, self::$foreground_colors))
		{
			$color = self::CLI_COLOR_DEFAULT;
		}

		return "\033[" . $color . "m" . $text . "\033[0m";
	}
}