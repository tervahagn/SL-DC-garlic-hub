<?php

namespace Tests\Unit\Modules\Playlists\Services;

use App\Modules\Playlists\Helper\Widgets\ContentDataPreparer;
use App\Modules\Playlists\Services\ItemsService;
use App\Modules\Playlists\Services\WidgetsService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class WidgetsServiceTest extends TestCase
{
	private ItemsService&MockObject $itemsServiceMock;
	private ContentDataPreparer&MockObject $contentDataMock;
	private LoggerInterface&MockObject $loggerMock;
	private WidgetsService $widgetsService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->itemsServiceMock = $this->createMock(ItemsService::class);
		$this->contentDataMock = $this->createMock(ContentDataPreparer::class);
		$this->loggerMock = $this->createMock(LoggerInterface::class);

		$this->widgetsService = new WidgetsService(
			$this->itemsServiceMock,
			$this->contentDataMock,
			$this->loggerMock
		);
	}

	#[Group('units')]
	public function testGetErrorTextReturnsDefaultEmptyValue(): void
	{
		$this->assertSame('', $this->widgetsService->getErrorText());
	}

	#[Group('units')]
	public function testFetchWidgetByItemIdReturnsExpectedResult(): void
	{
		$itemId = 1;
		$UID = 12;
		$itemData = [
			'config_data' => '<config></config>',
			'content_data' => serialize(['key' => 'value']),
			'item_name' => 'Test Widget',
			'mimetype' => 'application/widget'
		];
		$this->widgetsService->setUID($UID);
		$this->itemsServiceMock->expects($this->once())->method('setUID')
			->with($UID);
		$this->itemsServiceMock->expects($this->once())->method('fetchItemById')
			->with($itemId)
			->willReturn($itemData);

		$this->contentDataMock->expects($this->once())->method('determinePreferences')
			->with('<config></config>')
			->willReturn(['prefs_key' => 'prefs_value']);


		$expected = [
			'item_id' => $itemId,
			'values' => ['key' => 'value'],
			'preferences' => ['prefs_key' => 'prefs_value'],
			'item_name' => 'Test Widget',
		];
		$this->assertSame($expected, $this->widgetsService->fetchWidgetByItemId($itemId));
	}

	#[Group('units')]
	public function testFetchWidgetByItemIdReturnsEmptyWhenNoWidget(): void
	{
		$itemId = 1;
		$UID = 12;
		$itemData = [
			'config_data' => '<config></config>',
			'content_data' => serialize(['key' => 'value']),
			'item_name' => 'Test Widget',
			'mimetype' => 'text/html'
		];
		$this->widgetsService->setUID($UID);
		$this->itemsServiceMock->expects($this->once())->method('setUID')
			->with($UID);
		$this->itemsServiceMock->expects($this->once())->method('fetchItemById')
			->with($itemId)
			->willReturn($itemData);

		$this->contentDataMock->expects($this->never())->method('determinePreferences');
		$this->loggerMock->expects($this->once())->method('error')
			->with($this->stringContains('Error widget fetch: Not a widget item.'));

		$this->assertEmpty($this->widgetsService->fetchWidgetByItemId($itemId));

	}

	#[Group('units')]
	public function testSaveWidgetSuccessfullySaves(): void
	{
		$itemId = 1;
		$requestData = ['key' => 'value'];
		$configData = '<config></config>';
		$serializedContent = serialize(['prepared_key' => 'prepared_value']);

		$itemData = [
			'config_data' => $configData,
			'content_data' => null,
			'item_name' => 'Test Widget',
			'mimetype' => 'application/widget'
		];

		$this->widgetsService->setUID(14);
		$this->itemsServiceMock->expects($this->once())->method('setUID')
			->with(14);

		$this->itemsServiceMock->expects($this->once())->method('fetchItemById')
			->with($itemId)
			->willReturn($itemData);

		$this->contentDataMock->expects($this->once())->method('prepareContentData')
			->with($configData, $requestData, false)
			->willReturn(['prepared_key' => 'prepared_value']);

		$this->itemsServiceMock->expects($this->once())
			->method('updateField')
			->with($itemId, 'content_data', $serializedContent);

		$this->assertTrue($this->widgetsService->saveWidget($itemId, $requestData));
	}

	#[Group('units')]
	public function testSaveWidgetHandlesExceptionAndFails(): void
	{
		$itemId = 1;
		$requestData = ['key' => 'value'];
		$exceptionMessage = 'Error saving data';

		$this->widgetsService->setUID(14);
		$this->itemsServiceMock->expects($this->once())->method('setUID')
			->with(14);

		$this->itemsServiceMock->expects($this->once())
			->method('fetchItemById')
			->with($itemId)
			->willThrowException(new \Exception($exceptionMessage));

		$this->loggerMock->expects($this->once())
			->method('error')
			->with($this->stringContains($exceptionMessage));

		$this->assertFalse($this->widgetsService->saveWidget($itemId, $requestData));
		$this->assertSame($exceptionMessage, $this->widgetsService->getErrorText());
	}
}
