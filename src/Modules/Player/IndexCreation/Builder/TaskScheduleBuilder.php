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


namespace App\Modules\Player\IndexCreation\Builder;

class TaskScheduleBuilder
{
	private bool $isReplacedSomething = false;
	private array $template = [];

	public function isReplacedSomething(): bool
	{
		return $this->isReplacedSomething;
	}

	public function getTemplate(): array
	{
		return $this->template;
	}

	public function replaceRebootBlock(): static
	{
		$this->template['shutdown'][] = ['SHUTDOWN_TASK_ID', $this->generateTaskId()];

		return $this;
	}

	public function replaceClearWebcacheBlock(): static
	{
		$this->template['apply_command'][] = [
			'COMMAND_TASK_ID' => $this->generateTaskId(),
			'COMMAND' => 'clear_webcache'
		];
		return $this;
	}

	public function replaceClearCacheBlock(): static
	{
		$this->template['apply_command'][] = [
			'COMMAND_TASK_ID' => $this->generateTaskId(),
			'COMMAND' => 'clear_playercache'
		];

		return $this;
	}

	/**
	 * Creates the configuration part https://garlic-player.com/garlic-player/docs/essentials/maintenance-tasks/#configuration
	 * for the  Taskfile https://garlic-player.com/garlic-player/docs/essentials/maintenance-tasks/
	 *
	 * The configuration XML-file itself is created in ConfigurationController.php
	 */
	public function replaceUpdatesUrlsListBlock(array $ar_response): static
	{
		$this->template['urls_list'][] = [
			'URLS_LIST_TASK_ID' => $this->generateTaskId(),
			'URLS_LIST_FILE_URI' => $ar_response['file_url'],
			'URLS_LIST_FILE_LENGHT' => $ar_response['file_size'],
			'URLS_LIST_FILE_CHECKSUM' => $ar_response['md5_file']
		];

		return $this;
	}

	/**
	 * Creates the configuration part https://garlic-player.com/garlic-player/docs/essentials/maintenance-tasks/#configuration
	 * for the  Taskfile https://garlic-player.com/garlic-player/docs/essentials/maintenance-tasks/
	 *
	 * The configuration XML-file itself is created in ConfigurationController.php
	 */
	public function replaceConfigurationBlock(array $ar_response): static
	{
		$this->template['configuration'][] = [
			'CONFIGURATION_TASK_ID' => $this->generateTaskId(),
			'CONFIGURATION_FILE_URI' => $ar_response['file_url'],
			'CONFIGURATION_FILE_LENGHT' => $ar_response['file_size'],
			'CONFIGURATION_FILE_CHECKSUM' => $ar_response['md5_file']
		];

		return $this;
	}

	public function replaceFirmwareBlock(array $ar_response): static
	{
		$this->template['firmware'][] = [
			'FIRMWARE_TASK_ID' => $this->generateTaskId(),
			'FIRMWARE_FILE_URI' => $ar_response['file_url'],
			'FIRMWARE_TARGET_VERSION' => '1.0.0',
			'FIRMWARE_FILE_LENGHT' => $ar_response['file_size'],
			'FIRMWARE_FILE_CHECKSUM' => $ar_response['md5_file']
		];
		
		return $this;
	}

	protected function generateTaskId(): string
	{
		$this->isReplacedSomething = true;
		return uniqid(rand());
	}

}