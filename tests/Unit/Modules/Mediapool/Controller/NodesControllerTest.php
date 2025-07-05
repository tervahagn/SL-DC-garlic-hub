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

namespace Tests\Unit\Modules\Mediapool\Controller;

use App\Framework\Core\CsrfToken;
use App\Framework\Core\Session;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\DatabaseException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\Controller\NodesController;
use App\Modules\Mediapool\Services\NodesService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class NodesControllerTest extends TestCase
{
	private ServerRequestInterface&MockObject $requestMock;
	private ResponseInterface&MockObject $responseMock;
	private NodesService&MockObject $nodesServiceMock;
	private CsrfToken&MockObject $csrfTokenMock;
	private NodesController $controller;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->requestMock      = $this->createMock(ServerRequestInterface::class);
		$this->responseMock     = $this->createMock(ResponseInterface::class);
		$this->nodesServiceMock = $this->createMock(NodesService::class);
		$this->csrfTokenMock    = $this->createMock(CsrfToken::class);
		$this->controller       = new NodesController($this->nodesServiceMock, $this->csrfTokenMock);
	}

	/**
	 * @throws ModuleException
	 * @throws Exception
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testList(): void
	{
		$this->mockSession();
		$this->nodesServiceMock->expects($this->once())
			->method(PropertyHook::set('UID'))
			->with(1);

		$this->nodesServiceMock->expects($this->once())
			->method('getNodes')
			->with(0)
			->willReturn(['node1' => [], 'node2' => []]);

		$this->mockResponse(['node1' => [], 'node2' => []]);
		$this->controller->list($this->requestMock, $this->responseMock, []);
	}


	/**
	 * @throws CoreException
	 * @throws DatabaseException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 */
	#[Group('units')]
	public function testAdd(): void
	{
		$this->mockSession();
		$this->requestMock->method('getParsedBody')
			->willReturn(['name' => 'New Node']);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->nodesServiceMock->expects($this->once())
			->method(PropertyHook::set('UID'))
			->with(1);

		$this->nodesServiceMock->expects($this->once())
			->method('addNode')
			->with(0, 'New Node')
			->willReturn(123);

		$this->mockResponse(['success' => true, 'data' => ['id' => 123, 'new_name' => 'New Node']]);

		$this->controller->add($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws CoreException
	 * @throws DatabaseException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 */
	#[Group('units')]
	public function testAddFailToken(): void
	{
		$this->requestMock->method('getParsedBody')->willReturn([]);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(false);

		$this->nodesServiceMock->expects($this->never())->method(PropertyHook::set('UID'));
		$this->nodesServiceMock->expects($this->never())->method('addNode');

		$this->mockResponse(['success' => false, 'error_message' => 'Csrf token mismatch.']);
		$this->controller->add($this->requestMock, $this->responseMock);
	}


	/**
	 * @throws CoreException
	 * @throws DatabaseException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 */
	#[Group('units')]
	public function testAddFailNoName(): void
	{
		$this->requestMock->method('getParsedBody')->willReturn([]);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->nodesServiceMock->expects($this->never())->method(PropertyHook::set('UID'));
		$this->nodesServiceMock->expects($this->never())->method('addNode');

		$this->mockResponse(['success' => false, 'error_message' => 'node name is missing']);
		$this->controller->add($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testEdit(): void
	{
		$this->mockSession();
		$this->requestMock->method('getParsedBody')
			->willReturn(['name' => 'Updated Node', 'node_id' => 1]);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->nodesServiceMock->expects($this->once())
			->method(PropertyHook::set('UID'))
			->with(1);

		$this->nodesServiceMock->expects($this->once())
			->method('editNode')
			->with(1, 'Updated Node', null)
			->willReturn(1);

		$this->mockResponse(['success' => true, 'data' => ['id' => 1, 'new_name' => 'Updated Node', 'visibility' => null]]);

		$this->controller->edit($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testEditFailsNoParams(): void
	{
		$this->requestMock->method('getParsedBody')->willReturn([]);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->nodesServiceMock->expects($this->never())->method(PropertyHook::set('UID'));
		$this->nodesServiceMock->expects($this->never())->method('editNode');

		$this->mockResponse(['success' => false, 'error_message' => 'node name or id is missing']);
		$this->controller->edit($this->requestMock, $this->responseMock);
	}


	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testEditFailsCount(): void
	{
		$this->mockSession();
		$this->requestMock->method('getParsedBody')
			->willReturn(['name' => 'Updated Node', 'node_id' => 1]);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->nodesServiceMock->expects($this->once())
			->method(PropertyHook::set('UID'))
			->with(1);

		$this->nodesServiceMock->expects($this->once())
			->method('editNode')
			->willReturn(0);

		$this->mockResponse(['success' => false, 'error_message' => 'Edit node failed']);

		$this->controller->edit($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws Exception
	 * @throws DatabaseException
	 */
	#[Group('units')]
	public function testMove(): void
	{
		$this->mockSession();
		$this->requestMock->method('getParsedBody')
			->willReturn(['src_node_id' => 1, 'target_node_id' => 2, 'target_region' => 'region']);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->nodesServiceMock->expects($this->once())
			->method(PropertyHook::set('UID'))
			->with(1);

		$this->nodesServiceMock->expects($this->once())
			->method('moveNode')
			->with(1, 2, 'region')
			->willReturn(1);

		$this->mockResponse(['success' => true, 'data' => ['count_deleted_nodes' => 1]]);

		$this->controller->move($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws DatabaseException
	 * @throws Exception
	 */
	#[Group('units')]
	public function testMoveFailParams(): void
	{
		$this->requestMock->method('getParsedBody')->willReturn([]);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->nodesServiceMock->expects($this->never())->method(PropertyHook::set('UID'));
		$this->nodesServiceMock->expects($this->never())->method('moveNode');

		$this->mockResponse(['success' => false, 'error_message' => 'Source node, target node, or target region is missing']);

		$this->controller->move($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws CoreException
	 * @throws DatabaseException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 */
	#[Group('units')]
	public function testDelete(): void
	{
		$this->mockSession();
		$this->requestMock->method('getParsedBody')
			->willReturn(['node_id' => 1]);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->nodesServiceMock->expects($this->once())
			->method(PropertyHook::set('UID'))
			->with(1);

		$this->nodesServiceMock->expects($this->once())
			->method('deleteNode')
			->with(1)
			->willReturn(1);

		$this->mockResponse(['success' => true, 'data' => ['count_deleted_nodes' => 1]]);

		$this->controller->delete($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws CoreException
	 * @throws DatabaseException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 */
	#[Group('units')]
	public function testDeleteFailsNodeId(): void
	{
		$this->requestMock->method('getParsedBody')->willReturn([]);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->nodesServiceMock->expects($this->never())->method(PropertyHook::set('UID'));
		$this->nodesServiceMock->expects($this->never())->method('deleteNode');

		$this->mockResponse(['success' => false, 'error_message' => 'NodeId is missing']);

		$this->controller->delete($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws Exception
	 */
	private function mockSession(): void
	{
		$sessionMock = $this->createMock(Session::class);
		$this->requestMock ->expects($this->once())
			->method('getAttribute')
			->with('session')
			->willReturn($sessionMock);

		$sessionMock->method('get')->with('user')->willReturn(['UID' => 1]);
	}

	/**
	 * @param array<string,mixed> $data
	 * @throws Exception
	 */
	private function mockResponse(array $data): void
	{
		$streamInterfaceMock = $this->createMock(StreamInterface::class);
		$this->responseMock->expects($this->once())
			->method('getBody')
			->willReturn($streamInterfaceMock);

		$streamInterfaceMock->expects($this->once())
			->method('write')
			->with(json_encode($data));

		$this->responseMock ->expects($this->once())
			->method('withHeader')
			->with('Content-Type', 'application/json')
			->willReturnSelf();

		$this->responseMock ->expects($this->once())
			->method('withStatus')
			->with(200)
			->willReturnSelf();
	}
}
