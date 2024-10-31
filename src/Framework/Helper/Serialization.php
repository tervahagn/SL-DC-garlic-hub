<?php

namespace App\Framework\Helper;

class Serialization
{
	public static function unserializeSecure($value): array
	{
		$ar = @unserialize($value);
		return is_array($ar) ? $ar : [];
	}
}