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

namespace Tests\Unit\Modules\Playlists\Controller;

use App\Framework\Core\CsrfToken;
use App\Framework\Core\Session;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
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
	private CsrfToken&MockObject $csrfTokenMock;
	private StreamInterface&MockObject $streamInterfaceMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->itemsServiceMock      = $this->createMock(ItemsService::class);
		$this->insertItemFactoryMock = $this->createMock(InsertItemFactory::class);
		$this->requestMock           = $this->createMock(ServerRequestInterface::class);
		$this->responseMock          = $this->createMock(ResponseInterface::class);
		$this->streamInterfaceMock   = $this->createMock(StreamInterface::class);
		$this->sessionMock           = $this->createMock(Session::class);
		$this->csrfTokenMock    = $this->createMock(CsrfToken::class);

		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);

		$this->itemsController = new ItemsController($this->itemsServiceMock, $this->insertItemFactoryMock, $this->csrfTokenMock);
	}


	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
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

		$this->itemsController->loadItems($this->requestMock, $this->responseMock, $args);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testLoadItemsWithInvalidPlaylistId(): void
	{
		$args = [];

		$this->itemsServiceMock->expects($this->never())->method('loadItemsByPlaylistIdForComposer');

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->itemsController->loadItems($this->requestMock, $this->responseMock, $args);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testInsert(): void
	{
		$requestData = ['playlist_id' => 1, 'id' => 'item1', 'source' => 'video', 'position' => 5];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$insertItemMock = $this->createMock(AbstractInsertItem::class);
		$insertItemMock->expects($this->once())->method('insert')
			->with(1, 'item1', 5)
			->willReturn(['inserted_item_data']);

		$this->insertItemFactoryMock->method('create')->with('video')->willReturn($insertItemMock);
		$this->setServiceUIDMocks();
		$this->mockJsonResponse(['success' => true, 'data' => ['inserted_item_data']]);

		$this->itemsController->insert($this->requestMock, $this->responseMock);
	}

	#[Group('units')]
	public function testInsertInvalidCsrfToken(): void
	{
		$requestData = [];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(false);
		$this->mockJsonResponse(['success' => false, 'error_message' => 'CSRF token mismatch.']);

		$this->insertItemFactoryMock->expects($this->never())->method('create');

		$this->itemsController->insert($this->requestMock, $this->responseMock);
	}


	#[Group('units')]
	public function testInsertInvalidPlaylistId(): void
	{
		$requestData = [];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->insertItemFactoryMock->expects($this->never())->method('create');

		$this->itemsController->insert($this->requestMock, $this->responseMock);
	}

	#[Group('units')]
	public function testInsertInvalidContentId(): void
	{
		$requestData = ['playlist_id' => 1];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Content ID not valid.']);

		$this->insertItemFactoryMock->expects($this->never())->method('create');

		$this->itemsController->insert($this->requestMock, $this->responseMock);
	}

	#[Group('units')]
	public function testInsertInvalidSource(): void
	{
		$requestData = ['playlist_id' => 1, 'id' => 'item1'];

		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Source not valid.']);

		$this->insertItemFactoryMock->expects($this->never())->method('create');

		$this->itemsController->insert($this->requestMock, $this->responseMock);
	}

	#[Group('units')]
	public function testInsertInvalidPosition(): void
	{
		$requestData = ['playlist_id' => 1, 'id' => 'item1', 'source' => 'video'];

		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Position not valid.']);

		$this->itemsController->insert($this->requestMock, $this->responseMock);
	}

	#[Group('units')]
	public function testInsertFactoryReturnsNull(): void
	{
		$requestData = ['playlist_id' => 1, 'id' => 'item1', 'source' => 'video', 'position' => 5];

		$this->insertItemFactoryMock->method('create')->with('video')->willReturn(null);

		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Error inserting item.']);

		$this->itemsController->insert($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEdit(): void
	{
		$requestData = ['item_id' => 123, 'name' => 'item_name', 'value' => 'New Name'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->setServiceUIDMocks();
		$this->itemsServiceMock->expects($this->once())
			->method('updateField')
			->with(123, 'item_name', 'New Name')
			->willReturn(1);

		$this->mockJsonResponse(['success' => true, 'data' => '']);

		$this->itemsController->edit($this->requestMock, $this->responseMock);
	}


	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditWithValidItemDuration(): void
	{
		$requestData = ['item_id' => '123', 'name' => 'item_duration', 'value' => '300'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->setServiceUIDMocks();
		$this->itemsServiceMock->expects($this->once())
			->method('updateItemDuration')
			->with(123, 300)
			->willReturn(1);
		$this->itemsServiceMock->expects($this->once())->method('getItemDuration')->willReturn(300);

		$this->mockJsonResponse(['success' => true, 'data' => 300]);

		$this->itemsController->edit($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditWithUnaffected(): void
	{
		$requestData = ['item_id' => 123, 'name' => 'item_duration', 'value' => '300'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->setServiceUIDMocks();
		$this->itemsServiceMock->expects($this->once())
			->method('updateItemDuration')
			->with(123, 300)
			->willReturn(0);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Error updating item field: '.$requestData['name']. '.']);

		$this->itemsController->edit($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditWithInvalidCsrf(): void
	{
		$requestData = ['name' => 'item_name', 'value' => 'New Name'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(false);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'CSRF token mismatch.']);

		$this->itemsController->edit($this->requestMock, $this->responseMock);
	}


	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditWithEmptyItemId(): void
	{
		$requestData = ['name' => 'item_name', 'value' => 'New Name'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Item ID not valid.']);

		$this->itemsController->edit($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditWithEmptyParameterName(): void
	{
		$requestData = ['item_id' => 123, 'value' => 'New Name'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'No parameter name.']);

		$this->itemsController->edit($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditWithEmptyParameterValue(): void
	{
		$requestData = ['item_id' => 123, 'name' => 'item_name'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'No parameter value.']);

		$this->itemsController->edit($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditWithInvalidParameterName(): void
	{
		$requestData = ['item_id' => 123, 'name' => 'invalid_field', 'value' => 'Some Value'];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->setServiceUIDMocks();

		$this->mockJsonResponse(['success' => false, 'error_message' => 'No valid parametername.']);

		$this->itemsController->edit($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
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

		$this->itemsController->fetch($this->requestMock, $this->responseMock, $args);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFetchWithInvalidItemId(): void
	{
		$args = [];
		$this->itemsServiceMock->expects($this->never())->method('fetchItemById');

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Item ID not valid.']);

		$this->itemsController->fetch($this->requestMock, $this->responseMock, $args);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
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

		$this->itemsController->fetch($this->requestMock, $this->responseMock, $args);
	}

	/**
	 * @throws FrameworkException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdateItemOrders(): void
	{
		$requestData = ['playlist_id' => 1, 'items_positions' => [['id' => 1, 'position' => 2], ['id' => 2, 'position' => 1]]];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->setServiceUIDMocks();
		$this->itemsServiceMock->expects($this->once())
			->method('updateItemOrder')
			->with(1, $requestData['items_positions'])
		    ->willReturn(true);

		$this->mockJsonResponse(['success' => true]);

		$this->itemsController->updateItemOrders($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws FrameworkException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdateItemOrdersWithInvalidCsrf(): void
	{
		$requestData = ['items_positions' => [['id' => 1, 'position' => 2], ['id' => 2, 'position' => 1]]];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(false);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'CSRF token mismatch.']);

		$this->itemsServiceMock->expects($this->never())->method('updateItemOrder');

		$this->itemsController->updateItemOrders($this->requestMock, $this->responseMock);
	}


	/**
	 * @throws FrameworkException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdateItemOrdersWithMissingPlaylistId(): void
	{
		$requestData = ['items_positions' => [['id' => 1, 'position' => 2], ['id' => 2, 'position' => 1]]];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->itemsServiceMock->expects($this->never())->method('updateItemOrder');

		$this->itemsController->updateItemOrders($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws FrameworkException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdateItemOrdersWithMissingItemPositions(): void
	{
		$requestData = ['playlist_id' => 1];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Items Position array is not valid.']);

		$this->itemsServiceMock->expects($this->never())->method('updateItemOrder');

		$this->itemsController->updateItemOrders($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteSucceed(): void
	{
		$requestData = ['playlist_id' => 1, 'item_id' => 123];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->setServiceUIDMocks();

		$item = ['id' => 123, 'name' => 'Test Item'];
		$this->itemsServiceMock->expects($this->once())
			->method('delete')
			->with(1, 123)
			->willReturn($item);

		$this->mockJsonResponse(['success' => true, 'data' => $item]);

		$this->itemsController->delete($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteInvalidCsrf(): void
	{
		$requestData = ['item_id' => 123];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(false);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'CSRF token mismatch.']);

		$this->itemsServiceMock->expects($this->never())->method('delete');

		$this->itemsController->delete($this->requestMock, $this->responseMock);
	}


	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteWithMissingPlaylistId(): void
	{
		$requestData = ['item_id' => 123];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->itemsServiceMock->expects($this->never())->method('delete');

		$this->itemsController->delete($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteWithMissingItemId(): void
	{
		$requestData = ['playlist_id' => 1];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Item ID not valid.']);

		$this->itemsServiceMock->expects($this->never())->method('delete');

		$this->itemsController->delete($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteWithError(): void
	{
		$requestData = ['playlist_id' => 1, 'item_id' => 123];
		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

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

	/**
	 * @param array<string,mixed> $data
	 */
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
