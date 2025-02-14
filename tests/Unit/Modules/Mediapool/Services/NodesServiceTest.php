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

namespace Tests\Unit\App\Modules\Mediapool\Services;


use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\Repositories\NodesRepository;
use App\Modules\Mediapool\Services\NodesService;
use App\Modules\Mediapool\Services\AclValidator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class NodesServiceTest extends TestCase
{
	private $nodesRepositoryMock;
	private $aclValidatorMock;
	private $nodesService;

	protected function setUp(): void
	{
		$this->nodesRepositoryMock = $this->createMock(NodesRepository::class);
		$this->aclValidatorMock = $this->createMock(AclValidator::class);
		$this->nodesService = new NodesService($this->nodesRepositoryMock, $this->aclValidatorMock);
	}

	#[Group('units')]
	public function testIsModuleAdmin()
	{
		$uid = 123;
		$this->aclValidatorMock->expects($this->once())
			->method('isModuleAdmin')
			->with($uid)
			->willReturn(true);

		$result = $this->nodesService->isModuleAdmin($uid);
		$this->assertTrue($result);
	}

	#[Group('units')]
	public function testGetNodesRoot()
	{
		$parentId = 0;
		$nodes = [['node_id' => 1, 'name' => 'Root Node', 'children' => 0, 'parent_id' => 0, 'UID' => 123, 'visibility' => 1]];
		$this->nodesRepositoryMock->expects($this->once())
			->method('findAllRootNodes')
			->willReturn($nodes);

		$rights = ['create' => true, 'read' => true, 'edit' => true, 'share' => 'global'];
		$this->aclValidatorMock->expects($this->once())
			->method('checkDirectoryPermissions')
			->willReturn($rights);

		$this->nodesService->setUID(123);
		$result = $this->nodesService->getNodes($parentId);
		$this->assertCount(1, $result);
		$this->assertEquals('Root Node', $result[0]['title']);
	}

	#[Group('units')]
	public function testAddRootNode()
	{
		$uid = 123;
		$name = 'New Root Node';
		$this->nodesService->setUID($uid);

		$this->aclValidatorMock->expects($this->once())
			->method('isModuleAdmin')
			->with($uid)
			->willReturn(true);

		$this->nodesRepositoryMock->expects($this->once())
			->method('addRootNode')
			->with($uid, $name)
			->willReturn(1);

		$result = $this->nodesService->addNode(0, $name);
		$this->assertEquals(1, $result);
	}

	#[Group('units')]
	public function testAddRootNodeFails()
	{
		$uid = 123;
		$name = 'New Root Node';
		$this->nodesService->setUID($uid);

		$this->aclValidatorMock->expects($this->once())
			->method('isModuleAdmin')
			->with($uid)
			->willReturn(false);

		$this->nodesRepositoryMock->expects($this->never())
			->method('addRootNode');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('No rights to add root node.');

		$result = $this->nodesService->addNode(0, $name);
		$this->assertEquals(1, $result);
	}


	#[Group('units')]
	public function testAddSubNode()
	{
		$uid = 123;
		$parentNodeId = 1;
		$name = 'New Sub Node';

		$this->nodesService->setUID($uid);

		$parentNode = ['node_id' => 1, 'name' => 'Parent Node', 'parent_id' => 1, 'children' => 0, 'UID' => 123, 'visibility' => 1];
		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($parentNodeId)
			->willReturn($parentNode);

		$rights = ['create' => true, 'read' => true, 'edit' => true, 'share' => 'global'];
		$this->aclValidatorMock->expects($this->once())
			->method('checkDirectoryPermissions')
			->willReturn($rights);

		$this->nodesRepositoryMock->expects($this->once())
			->method('addSubNode')
			->with($uid, $name, $parentNode)
			->willReturn(2);

		$result = $this->nodesService->addNode($parentNodeId, $name);
		$this->assertEquals(2, $result);
	}

	#[Group('units')]
	public function testAddSubNodeFailsNopParentNode()
	{
		$uid = 123;
		$parentNodeId = 1;
		$name = 'New Sub Node';

		$this->nodesService->setUID($uid);

		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($parentNodeId)
			->willReturn([]);

		$rights = ['create' => true, 'read' => true, 'edit' => true, 'share' => 'global'];
		$this->aclValidatorMock->expects($this->never())
			->method('checkDirectoryPermissions');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Parent node not found');

		$this->nodesRepositoryMock->expects($this->never())
			->method('addSubNode');

		$this->nodesService->addNode($parentNodeId, $name);
	}

	#[Group('units')]
	public function testAddSubNodeFailsNoRights()
	{
		$uid = 123;
		$parentNodeId = 1;
		$name = 'New Sub Node';

		$this->nodesService->setUID($uid);

		$parentNode = ['node_id' => 1, 'name' => 'Parent Node', 'parent_id' => 1, 'children' => 0, 'UID' => 123, 'visibility' => 1];
		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($parentNodeId)
			->willReturn($parentNode);

		$rights = ['create' => false, 'read' => false, 'edit' => false, 'share' => ''];
		$this->aclValidatorMock->expects($this->once())
			->method('checkDirectoryPermissions')
			->willReturn($rights);

		$this->nodesRepositoryMock->expects($this->never())
			->method('addSubNode');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('No rights to add node under: Parent Node');

		$result = $this->nodesService->addNode($parentNodeId, $name);
		$this->assertEquals(2, $result);
	}


	public function testMoveNode()
	{
		$movedNodeId = 1;
		$targetNodeId = 2;
		$region = 'appendChild';
		$movedNode = ['node_id' => 1, 'name' => 'Moved Node', 'children' => 0, 'UID' => 123, 'visibility' => 1, 'parent_id' => 2];
		$targetNode = ['node_id' => 2, 'name' => 'Target Node', 'children' => 0, 'UID' => 123, 'visibility' => 1, 'parent_id' => 0];

		$this->nodesRepositoryMock->expects($this->exactly(2))
			->method('getNode')
			->willReturnOnConsecutiveCalls($movedNode, $targetNode);

		$this->nodesRepositoryMock->expects($this->once())
			->method('moveNode')
			->with($movedNode, $targetNode, $region);

		$result = $this->nodesService->moveNode($movedNodeId, $targetNodeId, $region);
		$this->assertEquals(1, $result);
	}

	public function testDeleteNode()
	{
		$nodeId = 1;
		$node = ['node_id' => 1, 'name' => 'Node to Delete', 'children' => 0, 'UID' => 123, 'visibility' => 1, 'root_id' => 1, 'rgt' => 2, 'lft' => 1];
		$rights = ['create' => true, 'edit' => true, 'delete' => true, 'share' => true];

		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($nodeId)
			->willReturn($node);

		$this->aclValidatorMock->expects($this->once())
			->method('checkDirectoryPermissions')
			->willReturn($rights);

		$this->nodesRepositoryMock->expects($this->once())
			->method('deleteSingleNode')
			->with($node);

		$result = $this->nodesService->deleteNode($nodeId);
		$this->assertEquals(1, $result);
	}

	public function testEditNode()
	{
		$nodeId = 1;
		$name = 'Updated Node Name';
		$visibility = 1;
		$node = ['node_id' => 1, 'name' => 'Node to Edit', 'children' => 0, 'UID' => 123, 'visibility' => 0];
		$rights = ['create' => true, 'edit' => true, 'delete' => true, 'share' => true];

		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($nodeId)
			->willReturn($node);

		$this->aclValidatorMock->expects($this->once())
			->method('checkDirectoryPermissions')
			->willReturn($rights);

		$this->aclValidatorMock->expects($this->once())
			->method('isModuleAdmin')
			->willReturn(true);

		$this->nodesRepositoryMock->expects($this->once())
			->method('update')
			->with($nodeId, ['name' => $name, 'visibility' => $visibility])
			->willReturn(1);

		$result = $this->nodesService->editNode($nodeId, $name, $visibility);
		$this->assertEquals(1, $result);
	}
}