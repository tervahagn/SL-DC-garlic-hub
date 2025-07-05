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


namespace Tests\Unit\Modules\Player\IndexCreation\Builder;

use App\Modules\Player\IndexCreation\Builder\TaskScheduleBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class TaskScheduleBuilderTest extends TestCase
{
	private TaskScheduleBuilder $taskScheduleBuilder;

	protected function setUp(): void
	{
		parent::setUp();
		$this->taskScheduleBuilder = new TaskScheduleBuilder();
	}

	#[Group('units')]
	public function testReplaceRebootBlock_AddsShutdownTaskAndSetsFlag(): void
	{
		static::assertFalse($this->taskScheduleBuilder->isReplacedSomething());
		$this->taskScheduleBuilder->replaceRebootBlock();

		$template = $this->taskScheduleBuilder->getTemplate();

		static::assertTrue($this->taskScheduleBuilder->isReplacedSomething());
		static::assertArrayHasKey('shutdown', $template);
		static::assertNotEmpty($template['shutdown']);
		static::assertArrayHasKey(0, $template['shutdown']);
		static::assertEquals('SHUTDOWN_TASK_ID', $template['shutdown'][0][0]);
	}

	#[Group('units')]
	public function testReplaceClearWebcacheBlock_AddsCommandTaskWithClearWebcache(): void
	{
		static::assertFalse($this->taskScheduleBuilder->isReplacedSomething());
		$this->taskScheduleBuilder->replaceClearWebcacheBlock();

		$template = $this->taskScheduleBuilder->getTemplate();

		static::assertTrue($this->taskScheduleBuilder->isReplacedSomething());
		static::assertArrayHasKey('apply_command', $template);
		static::assertNotEmpty($template['apply_command']);
		static::assertArrayHasKey(0, $template['apply_command']);
		static::assertArrayHasKey('COMMAND_TASK_ID', $template['apply_command'][0]);
		static::assertArrayHasKey('COMMAND', $template['apply_command'][0]);
		static::assertEquals('clear_webcache', $template['apply_command'][0]['COMMAND']);
	}

	#[Group('units')]
	public function testReplaceClearCacheBlock_AddsCommandTaskWithClearPlayerCache(): void
	{
		static::assertFalse($this->taskScheduleBuilder->isReplacedSomething());
		$this->taskScheduleBuilder->replaceClearCacheBlock();

		$template = $this->taskScheduleBuilder->getTemplate();

		static::assertTrue($this->taskScheduleBuilder->isReplacedSomething());
		static::assertArrayHasKey('apply_command', $template);
		static::assertNotEmpty($template['apply_command']);
		static::assertArrayHasKey(0, $template['apply_command']);
		static::assertArrayHasKey('COMMAND_TASK_ID', $template['apply_command'][0]);
		static::assertArrayHasKey('COMMAND', $template['apply_command'][0]);
		static::assertEquals('clear_playercache', $template['apply_command'][0]['COMMAND']);
	}


	#[Group('units')]
	public function testReplaceUpdatesUrlsListBlock_AddsUrlsListBlockToTemplate(): void
	{
		static::assertFalse($this->taskScheduleBuilder->isReplacedSomething());

		$ar_response = [
			'file_url' => 'https://example.com/file/url',
			'file_size' => 123456,
			'md5_file' => 'd41d8cd98f00b204e9800998ecf8427e'
		];

		$this->taskScheduleBuilder->replaceUpdatesUrlsListBlock($ar_response);

		$template = $this->taskScheduleBuilder->getTemplate();

		static::assertTrue($this->taskScheduleBuilder->isReplacedSomething());
		static::assertArrayHasKey('urls_list', $template);
		static::assertNotEmpty($template['urls_list']);
		static::assertArrayHasKey(0, $template['urls_list']);
		static::assertArrayHasKey('URLS_LIST_TASK_ID', $template['urls_list'][0]);
		static::assertArrayHasKey('URLS_LIST_FILE_URI', $template['urls_list'][0]);
		static::assertArrayHasKey('URLS_LIST_FILE_LENGTH', $template['urls_list'][0]);
		static::assertArrayHasKey('URLS_LIST_FILE_CHECKSUM', $template['urls_list'][0]);
		static::assertEquals('https://example.com/file/url', $template['urls_list'][0]['URLS_LIST_FILE_URI']);
		static::assertEquals(123456, $template['urls_list'][0]['URLS_LIST_FILE_LENGTH']);
		static::assertEquals('d41d8cd98f00b204e9800998ecf8427e', $template['urls_list'][0]['URLS_LIST_FILE_CHECKSUM']);
	}

	#[Group('units')]
	public function testReplaceConfigurationBlock_AddsConfigurationBlockToTemplate(): void
	{
		static::assertFalse($this->taskScheduleBuilder->isReplacedSomething());

		$ar_response = [
			'file_url' => 'https://example.com/config/file',
			'file_size' => 78910,
			'md5_file' => 'e99a18c428cb38d5f260853678922e03'
		];

		$this->taskScheduleBuilder->replaceConfigurationBlock($ar_response);

		$template = $this->taskScheduleBuilder->getTemplate();

		static::assertTrue($this->taskScheduleBuilder->isReplacedSomething());
		static::assertArrayHasKey('configuration', $template);
		static::assertNotEmpty($template['configuration']);
		static::assertArrayHasKey(0, $template['configuration']);
		static::assertArrayHasKey('CONFIGURATION_TASK_ID', $template['configuration'][0]);
		static::assertArrayHasKey('CONFIGURATION_FILE_URI', $template['configuration'][0]);
		static::assertArrayHasKey('CONFIGURATION_FILE_LENGTH', $template['configuration'][0]);
		static::assertArrayHasKey('CONFIGURATION_FILE_CHECKSUM', $template['configuration'][0]);
		static::assertEquals('https://example.com/config/file', $template['configuration'][0]['CONFIGURATION_FILE_URI']);
		static::assertEquals(78910, $template['configuration'][0]['CONFIGURATION_FILE_LENGTH']);
		static::assertEquals('e99a18c428cb38d5f260853678922e03', $template['configuration'][0]['CONFIGURATION_FILE_CHECKSUM']);
	}

	#[Group('units')]
	public function testReplaceFirmwareBlock_AddsFirmwareBlockToTemplate(): void
	{
		static::assertFalse($this->taskScheduleBuilder->isReplacedSomething());

		$ar_response = [
			'file_url' => 'https://example.com/firmware/file',
			'file_size' => 654321,
			'md5_file' => 'a15f8cd98f00b204e9800998ecf8527e'
		];

		$this->taskScheduleBuilder->replaceFirmwareBlock($ar_response);

		$template = $this->taskScheduleBuilder->getTemplate();

		static::assertTrue($this->taskScheduleBuilder->isReplacedSomething());
		static::assertArrayHasKey('firmware', $template);
		static::assertNotEmpty($template['firmware']);
		static::assertArrayHasKey(0, $template['firmware']);
		static::assertArrayHasKey('FIRMWARE_TASK_ID', $template['firmware'][0]);
		static::assertArrayHasKey('FIRMWARE_FILE_URI', $template['firmware'][0]);
		static::assertArrayHasKey('FIRMWARE_TARGET_VERSION', $template['firmware'][0]);
		static::assertArrayHasKey('FIRMWARE_FILE_LENGTH', $template['firmware'][0]);
		static::assertArrayHasKey('FIRMWARE_FILE_CHECKSUM', $template['firmware'][0]);
		static::assertEquals('https://example.com/firmware/file', $template['firmware'][0]['FIRMWARE_FILE_URI']);
		static::assertEquals(654321, $template['firmware'][0]['FIRMWARE_FILE_LENGTH']);
		static::assertEquals('a15f8cd98f00b204e9800998ecf8527e', $template['firmware'][0]['FIRMWARE_FILE_CHECKSUM']);
	}


}
