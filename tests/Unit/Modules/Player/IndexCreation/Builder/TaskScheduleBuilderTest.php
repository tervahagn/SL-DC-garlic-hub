<?php

namespace Tests\Unit\Modules\Player\IndexCreation\Builder;

use App\Modules\Player\IndexCreation\Builder\TaskScheduleBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class TaskScheduleBuilderTest extends TestCase
{
	private TaskScheduleBuilder $taskScheduleBuilder;

	protected function setUp(): void
	{
		$this->taskScheduleBuilder = new TaskScheduleBuilder();
	}

	#[Group('units')]
	public function testReplaceRebootBlock_AddsShutdownTaskAndSetsFlag(): void
	{
		$this->assertFalse($this->taskScheduleBuilder->isReplacedSomething());
		$this->taskScheduleBuilder->replaceRebootBlock();

		$template = $this->taskScheduleBuilder->getTemplate();

		$this->assertTrue($this->taskScheduleBuilder->isReplacedSomething());
		$this->assertArrayHasKey('shutdown', $template);
		$this->assertNotEmpty($template['shutdown']);
		$this->assertArrayHasKey(0, $template['shutdown']);
		$this->assertEquals('SHUTDOWN_TASK_ID', $template['shutdown'][0][0]);
	}

	#[Group('units')]
	public function testReplaceClearWebcacheBlock_AddsCommandTaskWithClearWebcache(): void
	{
		$this->assertFalse($this->taskScheduleBuilder->isReplacedSomething());
		$this->taskScheduleBuilder->replaceClearWebcacheBlock();

		$template = $this->taskScheduleBuilder->getTemplate();

		$this->assertTrue($this->taskScheduleBuilder->isReplacedSomething());
		$this->assertArrayHasKey('apply_command', $template);
		$this->assertNotEmpty($template['apply_command']);
		$this->assertArrayHasKey(0, $template['apply_command']);
		$this->assertArrayHasKey('COMMAND_TASK_ID', $template['apply_command'][0]);
		$this->assertArrayHasKey('COMMAND', $template['apply_command'][0]);
		$this->assertEquals('clear_webcache', $template['apply_command'][0]['COMMAND']);
	}

	#[Group('units')]
	public function testReplaceClearCacheBlock_AddsCommandTaskWithClearPlayerCache(): void
	{
		$this->assertFalse($this->taskScheduleBuilder->isReplacedSomething());
		$this->taskScheduleBuilder->replaceClearCacheBlock();

		$template = $this->taskScheduleBuilder->getTemplate();

		$this->assertTrue($this->taskScheduleBuilder->isReplacedSomething());
		$this->assertArrayHasKey('apply_command', $template);
		$this->assertNotEmpty($template['apply_command']);
		$this->assertArrayHasKey(0, $template['apply_command']);
		$this->assertArrayHasKey('COMMAND_TASK_ID', $template['apply_command'][0]);
		$this->assertArrayHasKey('COMMAND', $template['apply_command'][0]);
		$this->assertEquals('clear_playercache', $template['apply_command'][0]['COMMAND']);
	}


	#[Group('units')]
	public function testReplaceUpdatesUrlsListBlock_AddsUrlsListBlockToTemplate(): void
	{
		$this->assertFalse($this->taskScheduleBuilder->isReplacedSomething());

		$ar_response = [
			'file_url' => 'https://example.com/file/url',
			'file_size' => 123456,
			'md5_file' => 'd41d8cd98f00b204e9800998ecf8427e'
		];

		$this->taskScheduleBuilder->replaceUpdatesUrlsListBlock($ar_response);

		$template = $this->taskScheduleBuilder->getTemplate();

		$this->assertTrue($this->taskScheduleBuilder->isReplacedSomething());
		$this->assertArrayHasKey('urls_list', $template);
		$this->assertNotEmpty($template['urls_list']);
		$this->assertArrayHasKey(0, $template['urls_list']);
		$this->assertArrayHasKey('URLS_LIST_TASK_ID', $template['urls_list'][0]);
		$this->assertArrayHasKey('URLS_LIST_FILE_URI', $template['urls_list'][0]);
		$this->assertArrayHasKey('URLS_LIST_FILE_LENGTH', $template['urls_list'][0]);
		$this->assertArrayHasKey('URLS_LIST_FILE_CHECKSUM', $template['urls_list'][0]);
		$this->assertEquals('https://example.com/file/url', $template['urls_list'][0]['URLS_LIST_FILE_URI']);
		$this->assertEquals(123456, $template['urls_list'][0]['URLS_LIST_FILE_LENGTH']);
		$this->assertEquals('d41d8cd98f00b204e9800998ecf8427e', $template['urls_list'][0]['URLS_LIST_FILE_CHECKSUM']);
	}

	#[Group('units')]
	public function testReplaceConfigurationBlock_AddsConfigurationBlockToTemplate(): void
	{
		$this->assertFalse($this->taskScheduleBuilder->isReplacedSomething());

		$ar_response = [
			'file_url' => 'https://example.com/config/file',
			'file_size' => 78910,
			'md5_file' => 'e99a18c428cb38d5f260853678922e03'
		];

		$this->taskScheduleBuilder->replaceConfigurationBlock($ar_response);

		$template = $this->taskScheduleBuilder->getTemplate();

		$this->assertTrue($this->taskScheduleBuilder->isReplacedSomething());
		$this->assertArrayHasKey('configuration', $template);
		$this->assertNotEmpty($template['configuration']);
		$this->assertArrayHasKey(0, $template['configuration']);
		$this->assertArrayHasKey('CONFIGURATION_TASK_ID', $template['configuration'][0]);
		$this->assertArrayHasKey('CONFIGURATION_FILE_URI', $template['configuration'][0]);
		$this->assertArrayHasKey('CONFIGURATION_FILE_LENGTH', $template['configuration'][0]);
		$this->assertArrayHasKey('CONFIGURATION_FILE_CHECKSUM', $template['configuration'][0]);
		$this->assertEquals('https://example.com/config/file', $template['configuration'][0]['CONFIGURATION_FILE_URI']);
		$this->assertEquals(78910, $template['configuration'][0]['CONFIGURATION_FILE_LENGTH']);
		$this->assertEquals('e99a18c428cb38d5f260853678922e03', $template['configuration'][0]['CONFIGURATION_FILE_CHECKSUM']);
	}

	#[Group('units')]
	public function testReplaceFirmwareBlock_AddsFirmwareBlockToTemplate(): void
	{
		$this->assertFalse($this->taskScheduleBuilder->isReplacedSomething());

		$ar_response = [
			'file_url' => 'https://example.com/firmware/file',
			'file_size' => 654321,
			'md5_file' => 'a15f8cd98f00b204e9800998ecf8527e'
		];

		$this->taskScheduleBuilder->replaceFirmwareBlock($ar_response);

		$template = $this->taskScheduleBuilder->getTemplate();

		$this->assertTrue($this->taskScheduleBuilder->isReplacedSomething());
		$this->assertArrayHasKey('firmware', $template);
		$this->assertNotEmpty($template['firmware']);
		$this->assertArrayHasKey(0, $template['firmware']);
		$this->assertArrayHasKey('FIRMWARE_TASK_ID', $template['firmware'][0]);
		$this->assertArrayHasKey('FIRMWARE_FILE_URI', $template['firmware'][0]);
		$this->assertArrayHasKey('FIRMWARE_TARGET_VERSION', $template['firmware'][0]);
		$this->assertArrayHasKey('FIRMWARE_FILE_LENGTH', $template['firmware'][0]);
		$this->assertArrayHasKey('FIRMWARE_FILE_CHECKSUM', $template['firmware'][0]);
		$this->assertEquals('https://example.com/firmware/file', $template['firmware'][0]['FIRMWARE_FILE_URI']);
		$this->assertEquals(654321, $template['firmware'][0]['FIRMWARE_FILE_LENGTH']);
		$this->assertEquals('a15f8cd98f00b204e9800998ecf8527e', $template['firmware'][0]['FIRMWARE_FILE_CHECKSUM']);
	}


}
