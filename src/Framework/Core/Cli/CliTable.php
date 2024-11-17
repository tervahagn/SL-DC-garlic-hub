<?php
namespace App\Framework\Core\Cli;

class CliTable
{
	/**
	 * @param array $data
	 * @return string
	 */
	public static function buildTable(array $data): string
	{
		// Find longest string in each column
		$columns = self::determineMaxColumnLength($data);
		return self::renderTable($data, $columns);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected static function determineMaxColumnLength(array $data): array
	{
		$columns = array();

		foreach ($data as $row)
		{
			foreach ($row as $cell_key => $cell)
			{
				$length = mb_strlen($cell) + 3; // adding some extra space

				if (empty($columns[$cell_key]) || $columns[$cell_key] < $length)
				{
					$columns[$cell_key] = $length;
				}
			}
		}

		return $columns;
	}

	/**
	 * @param array     $data
	 * @param array     $columns
	 * @return string
	 */
	protected static function renderTable(array $data, array $columns): string
	{
		$table      = '';

		foreach ($data as $row_key => $row)
		{
			$table .= '|';

			foreach ($row as $cell_key => $cell)
			{
				$table .= self::mb_str_pad($cell, $columns[$cell_key]) . '|';
			}
			$table .= PHP_EOL;

			if ($row_key === 0)
			{
				$table .= self::drawHorizontalLine($row,$columns);
			}
		}
		return $table;
	}

	/**
	 * @param array $rows
	 * @param array $columns
	 * @return string
	 */
	protected static function drawHorizontalLine(array $rows, array $columns): string
	{
		$line = '';

		foreach($rows as $cell_key => $cell)
		{
			$line .= str_repeat('-', $columns[$cell_key]);
		}

		return $line . PHP_EOL;
	}

	/**
	 * multi byte version for str_pad
	 * from comments on: http://php.net/manual/en/function.str-pad.php
	 *
	 * @param string $str
	 * @param int    $pad_len
	 * @param string $pad_str
	 * @param int    $dir
	 * @param null   $encoding
	 *
	 * @return string
	 */
	protected static function mb_str_pad(string $str, int $pad_len, string $pad_str = ' ', int $dir = STR_PAD_RIGHT, $encoding = NULL): string
	{
		$encoding   = $encoding === NULL ? mb_internal_encoding() : $encoding;
		$padBefore  = $dir === STR_PAD_BOTH || $dir === STR_PAD_LEFT;
		$padAfter   = $dir === STR_PAD_BOTH || $dir === STR_PAD_RIGHT;

		$pad_len -= mb_strlen($str, $encoding);

		$targetLen      = $padBefore && $padAfter ? $pad_len / 2 : $pad_len;
		$strToRepeatLen = mb_strlen($pad_str, $encoding);
		$repeatTimes    = ceil($targetLen / $strToRepeatLen);

		$repeatedString = str_repeat($pad_str, max(0, $repeatTimes)); // safe if used with valid utf-8 strings
		$before         = $padBefore ? mb_substr($repeatedString, 0, floor($targetLen), $encoding) : '';
		$after          = $padAfter ? mb_substr($repeatedString, 0, ceil($targetLen), $encoding) : '';
		return $before . $str . $after;
	}
}