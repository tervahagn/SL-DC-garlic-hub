<?php

use Defuse\Crypto\Key;
require_once __DIR__ . '/vendor/autoload.php';

try {
	echo "Starting installation process...\n";

	$requiredDirs = [
		__DIR__ . '/var',
		__DIR__ . '/var/cache',
		__DIR__ . '/var/logs',
		__DIR__ . '/var/keys',
		__DIR__ . '/public/var/mediapool',
		__DIR__ . '/public/var/mediapool/thumbs',
		__DIR__ . '/public/var/mediapool/originals',
	];

	foreach ($requiredDirs as $dir)
	{
		if (!is_dir($dir))
		{
			echo "Creating directory: $dir\n";
			if (!mkdir($dir, 0775, true) && !is_dir($dir))
				die("Error: Unable to create directory: $dir\n");

		//	if (!chown($dir, 'www-data') || !chgrp($dir, 'www-data'))
		//		die("Error: Unable to set owner/group to www-data for directory: $dir\n");

		}
		else
			echo "Directory already exists: $dir\n";
	}

	foreach ($requiredDirs as $dir) {
		if (!is_writable($dir))
			die("Error: Directory is not writable: $dir\n");

	}
	echo "Directory structure and permissions verified.\n";

	echo "create metadata for cli ...\n";
	$output = shell_exec('php bin/cli.php --update 2>&1');

	echo "Migrating database...\n";
	$output = shell_exec('php bin/console.php db:migrate 2>&1');
	echo "Database created.\n";

	echo "Create crypto keys...\n";
	$config = ["private_key_bits" => 2048, "private_key_type" => OPENSSL_KEYTYPE_RSA];
	$res = openssl_pkey_new($config);
	openssl_pkey_export($res, $privateKey);

	$details = openssl_pkey_get_details($res);
	$publicKey = $details['key'];
	file_put_contents('var/keys/private.key', $privateKey);
	file_put_contents('var/keys/public.key', $publicKey);

	$encryptionKey = Key::createNewRandomKey();
	file_put_contents('var/keys/encryption.key', $encryptionKey->saveToAsciiSafeString());
	echo "Keys sucessfully created!\n";
}
catch (\Exception $e)
{
	echo 'Failed: '. $e->getMessage();
}