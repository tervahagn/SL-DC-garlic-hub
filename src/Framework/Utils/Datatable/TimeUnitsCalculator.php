<?php

namespace App\Framework\Utils\Datatable;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use DateInterval;
use DateTime;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Create human-readable times
 */
class TimeUnitsCalculator
{
	private int $lastAccessTimeStamp = 0;
	private DateInterval $interval;
	private Translator $translator;

	public function __construct(Translator $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @throws FrameworkException
	 */
	public function calculateLastAccess(DateTime $currentTime, DateTime $lastAccess): static
	{
		$this->lastAccessTimeStamp = $currentTime->getTimestamp() - $lastAccess->getTimestamp();
		if ($this->lastAccessTimeStamp < 0)
			throw new FrameworkException('Negative time difference.');

		$this->interval = $currentTime->diff($lastAccess);

		return $this;
	}

	public function getLastAccessTimeStamp(): int
	{
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
		if ($this->interval->y > 0)
		{
			$number = $this->interval->y;
			$time_unit = 'years';
		}
		elseif ($this->interval->m > 0)
		{
			$number = $this->interval->m;
			$time_unit = 'months';
		}
		elseif ($this->interval->d > 0)
		{
			$number = $this->interval->d;
			$time_unit = 'days';
		}
		elseif ($this->interval->h > 0) {
			$number = $this->interval->h;
			$time_unit = 'hours';
		}
		elseif ($this->interval->i > 0)
		{
			$number = $this->interval->i;
			$time_unit = 'minutes';
		}
		else
		{
			$number = $this->interval->s;
			$time_unit = 'seconds';
		}
		return $this->translator->translateArrayWithPlural($time_unit, 'time_unit_ago', 'main', $number);
	}

}