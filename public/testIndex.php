<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

require '../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

$client    = new Client();
$targetUrl = 'https://garlic-hub.ddev.site/smil-index';
$options   = [
	'headers' => [
		'X-Signage-Agent' => 'GAPI/1.0 (UUID:15920d5d-7e68-4a61-a145-15b58b6d2090; NAME:Screenlite Web Test) screenlite-web/0.0.1 (MODEL:ScreenliteWeb)',
	],
];

try
{
	$response   = $client->request('GET', $targetUrl, $options);
	$statusCode = $response->getStatusCode();
	$body       = (string) $response->getBody();

	echo "Status Code: " . $statusCode . "\n";
	echo "Response Body:\n" . $body . "\n";
	echo "\n--- Sent successful! ---\n";

}
catch (ConnectException $e)
{
	echo 'Connection error: ' . $e->getMessage() . "\n";
}
catch (RequestException $e)
{
	echo 'Request error:  '. $e->getMessage() . "\n";
	if ($e->hasResponse())
		echo "Response body:\n" .  $e->getResponse()->getBody() . "\n";

}
catch (GuzzleException $e)
{
	echo 'Guzzle error:  '. $e->getMessage() . "\n";
}
catch (Exception $e)
{
	echo "Unexpected error: " . $e->getMessage() . "\n";
}
