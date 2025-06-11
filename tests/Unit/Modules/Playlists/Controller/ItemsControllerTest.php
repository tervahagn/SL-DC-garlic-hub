<?php

namespace Tests\Unit\Modules\Playlists\Controller;

use App\Framework\Core\Session;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Controller\ItemsController;
use App\Modules\Playlists\Services\InsertItems\AbstractInsertItem;
use App\Modules\Playlists\Services\InsertItems\InsertItemFactory;
use App\Modules\Playlists\Services\ItemsService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class ItemsControllerTest extends TestCase
{
	private ItemsController $itemsController;
	private ItemsService&MockObject $itemsServiceMock;
	private InsertItemFactory&MockObject $insertItemFactoryMock;
	private ResponseInterface&MockObject $responseMock;
	private ServerRequestInterface&MockObject $requestMock;
	private Session&MockObject $sessionMock;
	private StreamInterface&MockObject $streamInterfaceMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->itemsServiceMock      = $this->createMock(ItemsService::class);
		$this->insertItemFactoryMock = $this->createMock(InsertItemFactory::class);
		$this->requestMock           = $this->createMock(ServerRequestInterface::class);
		$this->responseMock          = $this->createMock(ResponseInterface::class);
		$this->streamInterfaceMock   = $this->createMock(StreamInterface::class);
		$this->sessionMock           = $this->createMock(Session::class);

		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);

		$this->itemsController = new ItemsController($this->itemsServiceMock, $this->insertItemFactoryMock);
	}


	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testLoadItemsWithValidPlaylistId(): void
	{
		$args = ['playlist_id' => 56];

		$this->setServiceUIDMocks();

		$list = ['some_stuff', 'some_other_stuff', 'and_more_stuff'];
		$this->itemsServiceMock->expects($this->once())
			->method('loadItemsByPlaylistIdForComposer')
			->with(56)
			->willReturn($list);
		$this->mockJsonResponse(['success' => true, 'data' =>  $list]);

		$response = $this->itemsController->loadItems($this->requestMock, $this->responseMock, $args);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testLoadItemsWithInvalidPlaylistId(): void
	{
		$args = [];

		$this->itemsServiceMock->expects($this->never())->method('loadItemsByPlaylistIdForComposer');

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$response = $this->itemsController->loadItems($this->requestMock, $this->responseMock, $args);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testInsert(): void
	{
		$requestData = ['playlist_id' => 1, 'id' => 'item1', 'source' => 'video', 'position' => 5];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$insertItemMock = $this->createMock(AbstractInsertItem::class);
		$insertItemMock->expects($this->once())->method('insert')
			->with(1, 'item1', 5)
			->willReturn(['inserted_item_data']);

		$this->insertItemFactoryMock->method('create')->with('video')->willReturn($insertItemMock);
		$this->setServiceUIDMocks();
		$this->mockJsonResponse(['success' => true, 'data' => ['inserted_item_data']]);

		$response = $this->itemsController->insert($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testInsertInvalidPlaylistId(): void
	{
		$requestData = [];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->insertItemFactoryMock->expects($this->never())->method('create');

		$response = $this->itemsController->insert($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testInsertInvalidContentId(): void
	{
		$requestData = ['playlist_id' => 1];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Content ID not valid.']);

		$this->insertItemFactoryMock->expects($this->never())->method('create');

		$response = $this->itemsController->insert($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testInsertInvalidSource(): void
	{
		$requestData = ['playlist_id' => 1, 'id' => 'item1'];

		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Source not valid.']);

		$this->insertItemFactoryMock->expects($this->never())->method('create');

		$response = $this->itemsController->insert($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testInsertInvalidPosition(): void
	{
		$requestData = ['playlist_id' => 1, 'id' => 'item1', 'source' => 'video'];

		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Position not valid.']);

		$response = $this->itemsController->insert($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testInsertFactoryReturnsNull(): void
	{
		$requestData = ['playlist_id' => 1, 'id' => 'item1', 'source' => 'video', 'position' => 5];

		$this->insertItemFactoryMock->method('create')->with('video')->willReturn(null);

		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Error inserting item.']);

		$response = $this->itemsController->insert($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditWithValidItemName(): void
	{
		$requestData = ['item_id' => 123, 'name' => 'item_name', 'value' => 'New Name'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$this->setServiceUIDMocks();
		$this->itemsServiceMock->expects($this->once())
			->method('updateField')
			->with(123, 'item_name', 'New Name')
			->willReturn(1);

		$this->mockJsonResponse(['success' => true]);

		$response = $this->itemsController->edit($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditWithValidItemDuration(): void
	{
		$requestData = ['item_id' => 123, 'name' => 'item_duration', 'value' => '300'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$this->setServiceUIDMocks();
		$this->itemsServiceMock->expects($this->once())
			->method('updateField')
			->with(123, 'item_duration', 300)
			->willReturn(1);

		$this->mockJsonResponse(['success' => true]);

		$response = $this->itemsController->edit($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditWithUnaffected(): void
	{
		$requestData = ['item_id' => 123, 'name' => 'item_duration', 'value' => '300'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$this->setServiceUIDMocks();
		$this->itemsServiceMock->expects($this->once())
			->method('updateField')
			->with(123, 'item_duration', 300)
			->willReturn(0);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Error updating item field: '.$requestData['name']. '.']);

		$response = $this->itemsController->edit($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditWithEmptyItemId(): void
	{
		$requestData = ['name' => 'item_name', 'value' => 'New Name'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Item ID not valid.']);

		$response = $this->itemsController->edit($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditWithEmptyParameterName(): void
	{
		$requestData = ['item_id' => 123, 'value' => 'New Name'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'No parameter name.']);

		$response = $this->itemsController->edit($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditWithEmptyParameterValue(): void
	{
		$requestData = ['item_id' => 123, 'name' => 'item_name'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'No parameter value.']);

		$response = $this->itemsController->edit($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditWithInvalidParameterName(): void
	{
		$requestData = ['item_id' => 123, 'name' => 'invalid_field', 'value' => 'Some Value'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$this->setServiceUIDMocks();

		$this->mockJsonResponse(['success' => false, 'error_message' => 'No valid parametername.']);

		$response = $this->itemsController->edit($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFetchWithValidItemId(): void
	{
		$args = ['item_id' => 123];
		$this->setServiceUIDMocks();

		$item = ['id' => 123, 'name' => 'Test Item'];
		$this->itemsServiceMock->expects($this->once())
			->method('fetchItemById')
			->with(123)
			->willReturn($item);

		$this->mockJsonResponse(['success' => true, 'item' => $item]);

		$response = $this->itemsController->fetch($this->requestMock, $this->responseMock, $args);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFetchWithInvalidItemId(): void
	{
		$args = [];
		$this->itemsServiceMock->expects($this->never())->method('fetchItemById');

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Item ID not valid.']);

		$response = $this->itemsController->fetch($this->requestMock, $this->responseMock, $args);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFetchWithNonexistentItemId(): void
	{
		$args = ['item_id' => 999];
		$this->setServiceUIDMocks();

		$this->itemsServiceMock->expects($this->once())
			->method('fetchItemById')
			->with(999)
			->willReturn([]);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Item not found.']);

		$response = $this->itemsController->fetch($this->requestMock, $this->responseMock, $args);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdateItemOrdersWithValidData(): void
	{
		$requestData = ['playlist_id' => 1, 'items_positions' => [['id' => 1, 'position' => 2], ['id' => 2, 'position' => 1]]];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$this->setServiceUIDMocks();
		$this->itemsServiceMock->expects($this->once())
			->method('updateItemOrder')
			->with(1, $requestData['items_positions'])
		    ->willReturn(true);

		$this->mockJsonResponse(['success' => true]);

		$response = $this->itemsController->updateItemOrders($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdateItemOrdersWithMissingPlaylistId(): void
	{
		$requestData = ['items_positions' => [['id' => 1, 'position' => 2], ['id' => 2, 'position' => 1]]];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->itemsServiceMock->expects($this->never())->method('updateItemOrder');

		$response = $this->itemsController->updateItemOrders($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdateItemOrdersWithMissingItemPositions(): void
	{
		$requestData = ['playlist_id' => 1];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Items Position array is not valid.']);

		$this->itemsServiceMock->expects($this->never())->method('updateItemOrder');

		$response = $this->itemsController->updateItemOrders($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteWithValidData(): void
	{
		$requestData = ['playlist_id' => 1, 'item_id' => 123];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$this->setServiceUIDMocks();

		$item = ['id' => 123, 'name' => 'Test Item'];
		$this->itemsServiceMock->expects($this->once())
			->method('delete')
			->with(1, 123)
			->willReturn($item);

		$this->mockJsonResponse(['success' => true, 'data' => $item]);

		$response = $this->itemsController->delete($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteWithMissingPlaylistId(): void
	{
		$requestData = ['item_id' => 123];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->itemsServiceMock->expects($this->never())->method('delete');

		$response = $this->itemsController->delete($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteWithMissingItemId(): void
	{
		$requestData = ['playlist_id' => 1];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Item ID not valid.']);

		$this->itemsServiceMock->expects($this->never())->method('delete');

		$response = $this->itemsController->delete($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteWithError(): void
	{
		$requestData = ['playlist_id' => 1, 'item_id' => 123];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);

		$this->setServiceUIDMocks();

		$this->itemsServiceMock->expects($this->once())
			->method('delete')
			->with(1, 123)
			->willReturn([]);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Error deleting item.']);

		$this->itemsController->delete($this->requestMock, $this->responseMock);
	}

	private function setServiceUIDMocks(): void
	{
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);
		$this->itemsServiceMock->expects($this->once())->method('setUID')->with(456);
	}

	private function mockJsonResponse(array $data): void
	{
		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);
		$this->streamInterfaceMock->method('write')->with(json_encode($data));
		$this->responseMock->expects($this->once())->method('withHeader')
			->with('Content-Type', 'application/json')
			->willReturnSelf();
		$this->responseMock->method('withStatus')->with('200');
	}

}
