<?php

namespace Tests\Unit\Modules\Playlists\Helper\Widgets;

use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Widget\ConfigXML;
use App\Modules\Playlists\Helper\Widgets\ContentDataPreparer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContentDataPreparerTest extends TestCase
{
	private ConfigXML&MockObject $configXmlMock;
	private ContentDataPreparer $contentDataPreparer;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->configXmlMock = $this->createMock(ConfigXML::class);
		$this->contentDataPreparer = new ContentDataPreparer($this->configXmlMock);
	}

	/**
	 * @throws ModuleException
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testDeterminePreferencesSuccess(): void
	{
		$configData = '<config></config>';
		$preferences = [
			'preference1' => ['type' => 'text'],
			'preference2' => ['type' => 'integer']
		];

		$this->configXmlMock->method('load')
			->with($configData)
			->willReturnSelf();

		$this->configXmlMock->method('hasEditablePreferences')
			->willReturn(true);

		$this->configXmlMock->method('parseBasic')
			->willReturnSelf();

		$this->configXmlMock->method('parsePreferences')
			->willReturnSelf();

		$this->configXmlMock->method('getPreferences')
			->willReturn($preferences);

		$result = $this->contentDataPreparer->determinePreferences($configData);

		$this->assertSame($preferences, $result);
	}

	#[Group('units')]
	public function testPrepareContentDataSuccess(): void
	{
		$configData = '<config></config>';
		$requestData = ['preference1' => '<value>', 'preference2' => '123'];
		$preferences = [
			'preference1' => ['types' => 'combo'],
			'preference2' => ['types' => 'integer']
		];

		$this->configXmlMock->method('load')
			->with($configData)
			->willReturnSelf();
		$this->configXmlMock->method('hasEditablePreferences')
			->willReturn(true);
		$this->configXmlMock->method('parseBasic')
			->willReturnSelf();
		$this->configXmlMock->method('parsePreferences')
			->willReturnSelf();
		$this->configXmlMock->method('getPreferences')
			->willReturn($preferences);

		$result = $this->contentDataPreparer->prepareContentData($configData, $requestData);

		$this->assertSame(['preference1' => '&lt;value&gt;', 'preference2' => 123], $result);
	}

	#[Group('units')]
	public function testPrepareContentDataThrowsModuleExceptionForMandatoryField(): void
	{
		$configData = '<config></config>';
		$requestData = [];
		$preferences = [
			'preference1' => ['types' => 'text', 'mandatory' => 'true']
		];

		$this->configXmlMock->method('load')
			->with($configData)
			->willReturnSelf();
		$this->configXmlMock->method('hasEditablePreferences')
			->willReturn(true);
		$this->configXmlMock->method('parseBasic')
			->willReturnSelf();
		$this->configXmlMock->method('parsePreferences')
			->willReturnSelf();
		$this->configXmlMock->method('getPreferences')
			->willReturn($preferences);

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('preference1 is mandatory field.');

		$this->contentDataPreparer->prepareContentData($configData, $requestData);
	}

	#[Group('units')]
	public function testPrepareContentDataWithInitIgnoresMandatoryFields(): void
	{
		$configData = '<config></config>';
		$requestData = [];
		$preferences = [
			'preference1' => ['types' => 'text', 'mandatory' => 'true']
		];

		$this->configXmlMock->method('load')
			->with($configData)
			->willReturnSelf();
		$this->configXmlMock->method('hasEditablePreferences')
			->willReturn(true);
		$this->configXmlMock->method('parseBasic')
			->willReturnSelf();
		$this->configXmlMock->method('parsePreferences')
			->willReturnSelf();
		$this->configXmlMock->method('getPreferences')
			->willReturn($preferences);

		$result = $this->contentDataPreparer->prepareContentData($configData, $requestData, true);

		$this->assertSame([], $result);
	}

	/**
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testDeterminePreferencesThrowsModuleExceptionIfNoEditablePreferences(): void
	{
		$configData = '<config></config>';

		$this->configXmlMock->method('load')
			->with($configData)
			->willReturnSelf();

		$this->configXmlMock->method('hasEditablePreferences')
			->willReturn(false);

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Widget has no editable preferences.');

		$this->contentDataPreparer->determinePreferences($configData);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testDeterminePreferencesThrowsFrameworkExceptionOnParseError(): void
	{
		$configData = '<invalid></config>';

		$this->configXmlMock->method('load')
			->with($configData)
			->willThrowException(new FrameworkException('Invalid config XML.'));

		$this->expectException(FrameworkException::class);
		$this->expectExceptionMessage('Invalid config XML.');

		$this->contentDataPreparer->determinePreferences($configData);
	}
}
