<?php

namespace App\Framework\Utils\Datatable;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Create human-readable times
 */
class TimeUnitsCalculator
{
	const int MINUTE =  60;
	const int HOUR   =  3600;
	const int DAY    =  86400;
	const int WEEK   =  604800;
	const int MONTH  =  2592000;
	const int YEAR   =  31536000;

	private int $lastAccessTimeStamp = 0;
	private Translator $translator;

	public function __construct(Translator $translator)
	{
		$this->translator = $translator;
	}

	public function calculateLastAccess(int $currentTime, string $lastAccess): int
	{
		$this->lastAccessTimeStamp = $currentTime - strtotime($lastAccess);
		return $this->lastAccessTimeStamp;
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function printDistance(): string
	{
		$seconds = $this->lastAccessTimeStamp;
		if ($seconds < self::MINUTE)
		{
			$number    = $seconds;
			$time_unit = 'seconds';
		}
		else if ($seconds < self::HOUR)
		{
			$number    = $this->divide($seconds, self::MINUTE);
			$time_unit = 'minutes';
		}
		else if ($seconds < self::DAY)
		{
			$number    = $this->divide($seconds, self::HOUR);
			$time_unit = 'hours';
		}
		else if ($seconds < self::MONTH)
		{
			$number    = $this->divide($seconds, self::DAY);
			$time_unit = 'days';
		}
		else if ($seconds < self::YEAR)
		{
			$number    = $this->divide($seconds, self::MONTH);
			$time_unit = 'months';
		}
		else
		{
			$number    = $this->divide($seconds, self::YEAR);
			$time_unit = 'years';
		}

		return $this->translator->translateArrayWithPlural($time_unit, 'time_unit_ago', 'main', $number);
	}

	private function divide(int $number, int $divisor): int
	{
		return floor($number / $divisor);
	}

}