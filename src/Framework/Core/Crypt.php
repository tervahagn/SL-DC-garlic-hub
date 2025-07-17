<?php
declare(strict_types=1);

namespace App\Framework\Core;

use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;
use Exception;

class Crypt
{
	private readonly Key $encryptionKey;

	/**
	 * @throws BadFormatException
	 * @throws EnvironmentIsBrokenException
	 */
	public function __construct(String $encryptionKey)
	{
		$this->encryptionKey = Key::loadFromAsciiSafeString($encryptionKey);
	}

	public function getEncryptionKey(): Key
	{
		return $this->encryptionKey;
	}

	/**
	 * @throws Exception
	 */
	public function generateRandomString(int $length = 32): string
	{
		return bin2hex($this->generateRandomBytes($length));
	}

	/**
	 * @throws Exception
	 */
	public function generateRandomBytes(int $length = 32): string
	{
		if ($length < 1)
			$length = 32;

		return random_bytes($length);
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

	public function checkPassword(string $clearText, string $hash): bool
	{
		// todo: implement password_needs_rehash and update this to userMain DB
		return password_verify($clearText, $hash);
	}

	public function createPasswordHash(string $clearText):string
	{
		return password_hash($clearText, PASSWORD_DEFAULT);
	}

	public function createSha256Hash(string $clearText): string
	{
		return hash('sha256', $clearText);
	}

	public function createCrc32bHash(string $clearText): string
	{
		return hash('crc32b', $clearText);
	}

	public function createMd5Hash(string $clearText): string
	{
		return hash('md5', $clearText);
	}}