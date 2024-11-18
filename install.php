<?php

echo "Starting installation process...\n";

$requiredDirs = [
	__DIR__ . '/var',
	__DIR__ . '/var/cache',
	__DIR__ . '/var/logs',
];

foreach ($requiredDirs as $dir)
{
	if (!is_dir($dir))
	{
		echo "Creating directory: $dir\n";
		if (!mkdir($dir, 0775, true) && !is_dir($dir))
			die("Error: Unable to create directory: $dir\n");
	}
	else
		echo "Directory already exists: $dir\n";
}

foreach ($requiredDirs as $dir)
{
	if (!is_writable($dir))
		die("Error: Directory is not writable: $dir\n");

}
echo "Directory structure and permissions verified.\n";

echo "create metadata for cli ...\n";
$output = shell_exec('php bin/cli.php --update 2>&1');

echo "Migrating database...\n";
$output = shell_exec('php bin/cli.php --site migrate-database 2>&1');

echo "Database created.\n";
