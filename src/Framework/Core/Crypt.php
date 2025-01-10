<?php

namespace App\Framework\Core;

use Exception;

class Crypt
{

	/**
	 * @throws Exception
	 */
	public function generateRandomString(int $length = 32): string
	{
		return bin2hex(random_bytes($length / 2));
	}

	/**
	 * @throws Exception
	 */
	public function generatePassword(int $length = 8): string
	{
		$chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ123456789!@#$%*()_-=+;:,.?';
		$password = '';
		for ($i = 0; $i < $length; $i++)
		{
			$password .= $chars[random_int(0, strlen($chars) - 1)];
		}
		return $password;	}

	/**
	 * @throws Exception
	 */
	public function generateRandomNumber(int $places = 9): int
	{
		$min = 10 ** ($places - 1);
		$max = (10 ** $places) - 1;
		return random_int($min, $max);

	}

	public function checkPassword($clear_text, $hash): bool
	{
		return password_verify($clear_text, $hash);
	}

	public function createPasswordHash(string $clear_text):string
	{
		return password_hash($clear_text, PASSWORD_BCRYPT);
	}

	public function createSha256Hash(string $clear_text): string
	{
		return hash('sha256', $clear_text);
	}

	public function createCrc32bHash(string $clear_text): string
	{
		return hash('crc32b', $clear_text);
	}

	public function createMd5Hash(string $clear_text): string
	{
		return hash('md5', $clear_text);
	}}