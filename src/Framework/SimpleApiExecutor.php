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
declare(strict_types=1);


namespace App\Framework;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Throwable;

class SimpleApiExecutor
{
	public function __construct(
		protected readonly Client $httpClient,
		protected readonly LoggerInterface $logger
	) {}


	/**
	 * @param array<string,int|string> $options
	 * @return array{success:bool, error?:string, data?:string}
	 */
	public function executeApiRequest(string $method, string $endpoint, string $token, array $options = []): array
	{
		try
		{
			$endpoint .= '?access_token='. $token;
			$response = $this->httpClient->request($method, $endpoint, $options);

			if ($response->getStatusCode() !== 200)
			{
				$error = "API request failed: {$response->getStatusCode()}";
				$this->logger->error($error, [
					'endpoint' => $endpoint,
					'body' => $response->getBody()->getContents()
				]);
				return ['success' => false, 'error' => $error];
			}

			$data = json_decode($response->getBody()->getContents(), true) ?? [];
			return ['success' => true, 'data' => $data];

		}
		catch (Throwable $e)
		{
			$this->logger->error("API request error: {$e->getMessage()}");
			return ['success' => false, 'error' => $e->getMessage()];
		}
	}

	/**
	 * @param array<string,int|string> $options
	 * @return array{success:bool, error?:string, data?:string}
	 */
	public function executeAuth(string $endpoint,  array $options = []): array
	{
		try
		{
			$response = $this->httpClient->request('GET', $endpoint, $options);

			if ($response->getStatusCode() !== 200)
			{
				$error = "API request failed: {$response->getStatusCode()}";
				$this->logger->error($error, [
					'endpoint' => $endpoint,
					'body' => $response->getBody()->getContents()
				]);
				return ['success' => false, 'error' => $error];
			}

			$data = json_decode($response->getBody()->getContents(), true) ?? [];
			return ['success' => true, 'data' => $data];

		}
		catch (Throwable $e)
		{
			$this->logger->error("API request error: {$e->getMessage()}");
			return ['success' => false, 'error' => $e->getMessage()];
		}
	}

}