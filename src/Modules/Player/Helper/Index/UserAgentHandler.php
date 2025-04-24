<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


namespace App\Modules\Player\Helper\Index;

class UserAgentHandler
{
	private PlayerDetector $playerDetector;
	private array $info = [];

	public function __construct(PlayerDetector $playerDetector)
	{
		$this->playerDetector = $playerDetector;
	}

	public function getInfo(): array
	{
		return $this->info;
	}

	public function getInfoByValue($key)
	{
		if (array_key_exists($key, $this->info))
			return $this->info[$key];
		else
			return '';
	}

	public function parseUserAgent($userAggent): static
	{
		// ADAPI/1.0 (UUID:a8294bat-c28f-50af-f94o-800869af5854; NAME:Player with spaces in name) SK8855-ADAPI/2.0.5 (MODEL:XMP-330)
		if (preg_match('/([^ ]+) \(UUID:(.*?); NAME:(.*?)\) (.*?) \(MODEL:(.*?)\)/', $userAggent, $matches))
		{
			$this->info = [
				'uuid' => $matches[2],
				'firmware_version' => $matches[4],
				'name' => urldecode($matches[3]),
				'model' => $this->playerDetector->detectModelId($matches[5])->getModelId()
			];
		}
		// SmartAPI/1.0 (UUID:cc009f47-5a8d-42b4-af5a-1865710c05ba; NAME:05B200T100223; VERSION:v1.0.16; MODEL:TD-1050)
		elseif (preg_match('/([^ ]+) \(UUID:(.*?); NAME:(.*?); VERSION:(.*?); MODEL:(.*?)\)/', $userAggent, $matches))
		{
			$this->info = [
				'uuid' => $matches[2],
				'firmware_version' => $matches[4],
				'name' => urldecode($matches[3]),
				'model' => $this->playerDetector->detectModelId($matches[5])->getModelId()
			];
		}
		elseif (preg_match('/([^ ]+) \(UUID:(.*?)\) (.*?)-(.*?) \(MODEL:(.*?)\)/', $userAggent, $matches))
		{
			$this->info = [
				'uuid' => $matches[2],
				'firmware_version' => $matches[4],
				'name' => urldecode($matches[3]),
				'model' => $this->playerDetector->detectModelId($matches[5])->getModelId()
			];
		}
		else
		{
			$this->parseUserAgentFallback($userAggent);
		}
		return $this;
	}

	public function parseUserAgentFallback($userAgent)
	{
		$tmp  = mb_strstr($userAgent, 'UUID:');
		$uuid = mb_substr($tmp, 5, mb_strpos($tmp, ';') - 5);
		$tmp  = mb_strstr($userAgent, 'NAME:');
		$name = mb_substr($tmp, 5, mb_strpos($tmp, ')') - 5);
		$tmp  = mb_strstr($userAgent, ') ');
		$firmware = mb_substr($tmp, 2, mb_strpos($tmp, ' (') - 2);
		$tmp = mb_strstr($userAgent, 'MODEL:');
		$model = $this->playerDetector->detectModelId(mb_substr($tmp, 6, mb_strpos($tmp, ')') - 6))->getModelId();
		$this->info = [
			'uuid' => $uuid,
			'firmware_version' => $firmware,
			'name' => urldecode($name),
			'model' => $model
		];
		return $this;
	}
}