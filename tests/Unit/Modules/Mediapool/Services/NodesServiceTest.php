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

namespace Tests\Unit\Modules\Mediapool\Services;

use App\Framework\Database\NestedSets\Calculator;
use App\Framework\Database\NestedSets\Service;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\Repositories\NodesRepository;
use App\Modules\Mediapool\Services\MediaService;
use App\Modules\Mediapool\Services\NodesService;
use App\Modules\Mediapool\Services\AclValidator;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NodesServiceTest extends TestCase
{
	private NodesRepository&MockObject $nodesRepositoryMock;
	private Service&MockObject $nestedSetServiceMock;
	private MediaService&MockObject $mediaServiceMock;
	private AclValidator&MockObject $aclValidatorMock;
	private NodesService $nodesService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->nodesRepositoryMock = $this->createMock(NodesRepository::class);
		$this->aclValidatorMock = $this->createMock(AclValidator::class);
		$this->nestedSetServiceMock = $this->createMock(Service::class);
		$this->mediaServiceMock = $this->createMock(MediaService::class);

		$this->nodesService = new NodesService(
			$this->nodesRepositoryMock, $this->nestedSetServiceMock, $this->mediaServiceMock,$this->aclValidatorMock
		);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsModuleAdmin(): void
	{
		$uid = 123;
		$this->aclValidatorMock->expects($this->once())
			->method('isModuleAdmin')
			->with($uid)
			->willReturn(true);

		$result = $this->nodesService->isModuleAdmin($uid);
		$this->assertTrue($result);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testGetNodesRoot(): void
	{
		$parentId = 0;
		$nodes = [['node_id' => 1, 'name' => 'Root Node', 'children' => 0, 'parent_id' => 0, 'UID' => 123, 'visibility' => 1]];
		$this->nestedSetServiceMock->expects($this->once())
			->method('findAllRootNodes')
			->willReturn($nodes);

		$this->nestedSetServiceMock->expects($this->never())
			->method('findAllChildNodesByParentNode');

		$rights = ['create' => true, 'read' => true, 'edit' => true, 'share' => 'global'];
		$this->aclValidatorMock->expects($this->once())
			->method('checkDirectoryPermissions')
			->willReturn($rights);


		$this->nodesService->UID = 123;
		$result = $this->nodesService->getNodes($parentId);
		$this->assertCount(1, $result);
		$this->assertEquals('Root Node', $result[0]['title']);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testGetNodesSub(): void
	{
		$parentId = 21;
		$nodes = [['node_id' => 1, 'name' => 'Sub Node', 'children' => 0, 'parent_id' => 0, 'UID' => 123, 'visibility' => 1]];
		$this->nestedSetServiceMock->expects($this->never())
			->method('findAllRootNodes');

		$this->nestedSetServiceMock->expects($this->once())
			->method('findAllChildNodesByParentNode')
			->willReturn($nodes);

		$rights = ['create' => true, 'read' => true, 'edit' => true, 'share' => 'global'];
		$this->aclValidatorMock->expects($this->once())
			->method('checkDirectoryPermissions')
			->willReturn($rights);

		$this->nodesService->UID = 123;
		$result = $this->nodesService->getNodes($parentId);
		$this->assertCount(1, $result);
		$this->assertEquals('Sub Node', $result[0]['title']);
	}

	/**
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testAddRootNode(): void
	{
		$uid = 123;
		$name = 'New Root Node';
		$this->nodesService->UID = $uid;

		$this->aclValidatorMock->expects($this->once())
			->method('isModuleAdmin')
			->with($uid)
			->willReturn(true);

		$this->nestedSetServiceMock->expects($this->once())
			->method('addRootNode')
			->with($uid, $name)
			->willReturn(1);

		$result = $this->nodesService->addNode(0, $name);
		$this->assertEquals(1, $result);
	}

	/**
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testAddRootNodeFails(): void
	{
		$uid = 123;
		$name = 'New Root Node';
		$this->nodesService->UID = $uid;

		$this->aclValidatorMock->expects($this->once())
			->method('isModuleAdmin')
			->with($uid)
			->willReturn(false);

		$this->nestedSetServiceMock->expects($this->never())
			->method('addRootNode');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('No rights to add root node.');

		$result = $this->nodesService->addNode(0, $name);
		$this->assertEquals(1, $result);
	}


	/**
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testAddSubNode(): void
	{
		$uid = 123;
		$parentNodeId = 1;
		$name = 'New Sub Node';

		$this->nodesService->UID = $uid;

		$parentNode = ['node_id' => 1, 'name' => 'Parent Node', 'parent_id' => 1, 'children' => 0, 'UID' => 123, 'visibility' => 1];
		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($parentNodeId)
			->willReturn($parentNode);

		$rights = ['create' => true, 'read' => true, 'edit' => true, 'share' => 'global'];
		$this->aclValidatorMock->expects($this->once())
			->method('checkDirectoryPermissions')
			->willReturn($rights);

		$this->nestedSetServiceMock->expects($this->once())
			->method('addSubNode')
			->with($uid, $name, $parentNode)
			->willReturn(2);

		$result = $this->nodesService->addNode($parentNodeId, $name);
		$this->assertEquals(2, $result);
	}

	/**
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testAddSubNodeFailsNopParentNode(): void
	{
		$uid = 123;
		$parentNodeId = 1;
		$name = 'New Sub Node';

		$this->nodesService->UID = $uid;

		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($parentNodeId)
			->willReturn([]);

		$this->aclValidatorMock->expects($this->never())
			->method('checkDirectoryPermissions');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Parent node not found');

		$this->nestedSetServiceMock->expects($this->never())
			->method('addSubNode');

		$this->nodesService->addNode($parentNodeId, $name);
	}

	/**
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testAddSubNodeFailsNoRights(): void
	{
		$uid = 123;
		$parentNodeId = 1;
		$name = 'New Sub Node';

		$this->nodesService->UID = $uid;

		$parentNode = ['node_id' => 1, 'name' => 'Parent Node', 'parent_id' => 1, 'children' => 0, 'UID' => 123, 'visibility' => 1];
		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($parentNodeId)
			->willReturn($parentNode);

		$rights = ['create' => false, 'read' => false, 'edit' => false, 'share' => ''];
		$this->aclValidatorMock->expects($this->once())
			->method('checkDirectoryPermissions')
			->willReturn($rights);

		$this->nestedSetServiceMock->expects($this->never())
			->method('addSubNode');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('No rights to add node under: Parent Node');

		$result = $this->nodesService->addNode($parentNodeId, $name);
		$this->assertEquals(2, $result);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditNodeSucceed(): void
	{
		$nodeId = 1;
		$name = 'Updated Node Name';
		$this->nodesService->UID = 12;
		$visibility = 1;
		$node = ['node_id' => 1, 'name' => 'Node to Edit', 'parent_id' => 0, 'children' => 0, 'UID' => 123, 'visibility' => 0];
		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($nodeId)
			->willReturn($node);

		$rights = ['create' => true, 'read' => true, 'edit' => true, 'share' => 'global'];
		$this->aclValidatorMock->expects($this->once())
			->method('checkDirectoryPermissions')
			->willReturn($rights);

		$this->aclValidatorMock->expects($this->exactly(2))
			->method('isModuleAdmin')
			->willReturn(true);

		$this->nodesRepositoryMock->expects($this->once())
			->method('update')
			->with($nodeId, ['name' => $name, 'visibility' => $visibility])
			->willReturn(1);

		$result = $this->nodesService->editNode($nodeId, $name, $visibility);
		$this->assertEquals(1, $result);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditNodeFailsByNode(): void
	{
		$nodeId = 1;
		$name = 'Updated Node Name';
		$this->nodesService->UID = 12;
		$visibility = 1;
		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($nodeId)
			->willReturn([]);

		$this->aclValidatorMock->expects($this->never())
			->method('checkDirectoryPermissions');

		$this->aclValidatorMock->expects($this->never())
			->method('isModuleAdmin');

		$this->nodesRepositoryMock->expects($this->never())
			->method('update');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Parent node not found');

		$this->nodesService->editNode($nodeId, $name, $visibility);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditNodeFailsByRights(): void
	{
		$nodeId = 1;
		$name = 'Updated Node Name';
		$this->nodesService->UID = 12;
		$visibility = 1;
		$node = ['node_id' => 1, 'name' => 'Node to Edit', 'children' => 0, 'UID' => 123, 'visibility' => 0];
		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($nodeId)
			->willReturn($node);

		$rights = ['create' => false, 'read' => true, 'edit' => false, 'share' => ''];
		$this->aclValidatorMock->expects($this->once())
			->method('checkDirectoryPermissions')
			->willReturn($rights);

		$this->aclValidatorMock->expects($this->never())
			->method('isModuleAdmin');

		$this->nodesRepositoryMock->expects($this->never())
			->method('update');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('No rights to edit node Node to Edit');

		$this->nodesService->editNode($nodeId, $name, $visibility);
	}

	/**
	 * @throws ModuleException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testMoveNodeSucceed(): void
	{
		$movedNodeId = 1;
		$targetNodeId = 2;
		$region = Calculator::REGION_APPENDCHILD;
		$movedNode = ['node_id' => 1, 'name' => 'Moved Node', 'children' => 0, 'UID' => 123, 'visibility' => 1, 'parent_id' => 2];
		$targetNode = ['node_id' => 2, 'name' => 'Target Node', 'children' => 0, 'UID' => 123, 'visibility' => 1, 'parent_id' => 0];

		$this->nodesRepositoryMock->expects($this->exactly(2))
			->method('getNode')
			->willReturnOnConsecutiveCalls($movedNode, $targetNode);

		$this->nestedSetServiceMock->expects($this->once())
			->method('moveNode')
			->with($movedNode, $targetNode, $region);

		$result = $this->nodesService->moveNode($movedNodeId, $targetNodeId, $region);
		$this->assertEquals(1, $result);
	}

	/**
	 * @throws ModuleException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testMoveNodeFailsByRegion(): void
	{
		$movedNodeId  = 1;
		$targetNodeId = 2;
		$region       = 'Bäm';

		$this->nodesRepositoryMock->expects($this->never())->method('getNode');
		$this->nestedSetServiceMock->expects($this->never())->method('moveNode');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage($region.' is not supported');
		$this->nodesService->moveNode($movedNodeId, $targetNodeId, $region);
	}

	/**
	 * @throws ModuleException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testMoveNodeFailsByMovedNodeRootDir(): void
	{
		$movedNodeId = 1;
		$targetNodeId = 2;
		$region = Calculator::REGION_APPENDCHILD;
		$movedNode = ['node_id' => 1, 'name' => 'Moved Node', 'children' => 0, 'UID' => 123, 'visibility' => 1, 'parent_id' => 0];
		$targetNode = ['node_id' => 2, 'name' => 'Target Node', 'children' => 0, 'UID' => 123, 'visibility' => 1, 'parent_id' => 0];

		$this->nodesRepositoryMock->expects($this->exactly(2))
			->method('getNode')
			->willReturnOnConsecutiveCalls($movedNode, $targetNode);

		$this->nestedSetServiceMock->expects($this->never())->method('moveNode');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Moving root node is not allowed');

		$this->nodesService->moveNode($movedNodeId, $targetNodeId, $region);
	}

	/**
	 * @throws ModuleException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testMoveNodeFailsByTargetCreateRoot(): void
	{
		$movedNodeId = 1;
		$targetNodeId = 0;
		$region = Calculator::REGION_APPENDCHILD;
		$movedNode = ['node_id' => 1, 'name' => 'Moved Node', 'children' => 0, 'UID' => 123, 'visibility' => 1, 'parent_id' => 12];
		$targetNode = ['node_id' => 2, 'name' => 'Target Node', 'children' => 0, 'UID' => 123, 'visibility' => 1, 'parent_id' => 0];

		$this->nodesRepositoryMock->expects($this->exactly(2))
			->method('getNode')
			->willReturnOnConsecutiveCalls($movedNode, $targetNode);

		$this->nestedSetServiceMock->expects($this->never())->method('moveNode');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Create root node with a move is not allowed');

		$this->nodesService->moveNode($movedNodeId, $targetNodeId, $region);
	}

	/**
	 * @throws ModuleException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testMoveNodeFailsByTargetCreateRootWithBefore(): void
	{
		$movedNodeId = 1;
		$targetNodeId = 4;
		$region = Calculator::REGION_BEFORE;
		$movedNode = ['node_id' => 1, 'name' => 'Moved Node', 'children' => 0, 'UID' => 123, 'visibility' => 1, 'parent_id' => 12];
		$targetNode = ['node_id' => 2, 'name' => 'Target Node', 'children' => 0, 'UID' => 123, 'visibility' => 1, 'parent_id' => 0];

		$this->nodesRepositoryMock->expects($this->exactly(2))
			->method('getNode')
			->willReturnOnConsecutiveCalls($movedNode, $targetNode);

		$this->nestedSetServiceMock->expects($this->never())->method('moveNode');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Create root node with a move is not allowed');

		$this->nodesService->moveNode($movedNodeId, $targetNodeId, $region);
	}

	/**
	 * @throws ModuleException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testMoveNodeFailsByTargetCreateRootWithAfter(): void
	{
		$movedNodeId = 1;
		$targetNodeId = 4;
		$region = Calculator::REGION_AFTER;
		$movedNode = ['node_id' => 1, 'name' => 'Moved Node', 'children' => 0, 'UID' => 123, 'visibility' => 1, 'parent_id' => 12];
		$targetNode = ['node_id' => 2, 'name' => 'Target Node', 'children' => 0, 'UID' => 123, 'visibility' => 1, 'parent_id' => 0];

		$this->nodesRepositoryMock->expects($this->exactly(2))
			->method('getNode')
			->willReturnOnConsecutiveCalls($movedNode, $targetNode);

		$this->nestedSetServiceMock->expects($this->never())->method('moveNode');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Create root node with a move is not allowed');

		$this->nodesService->moveNode($movedNodeId, $targetNodeId, $region);
	}


	/**
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteOneNodeSucceed(): void
	{
		$nodeId = 1;
		$this->nodesService->UID = 354;
		$node = ['node_id' => 1, 'name' => 'Node to Delete', 'parent_id' => 12, 'children' => 0,
			'UID' => 123, 'visibility' => 1, 'root_id' => 1, 'rgt' => 2, 'lft' => 1];
		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($nodeId)
			->willReturn($node);

		$rights = ['create' => true, 'read' => true, 'edit' => true, 'share' => 'global'];
		$this->aclValidatorMock->expects($this->once())
			->method('checkDirectoryPermissions')
			->willReturn($rights);

		$this->nestedSetServiceMock->expects($this->once())
			->method('findAllSubNodeIdsByRootIdsAndPosition')
			->with($node['root_id'], $node['rgt'], $node['lft'])
			->willReturn([['hurz']]);


		$this->nestedSetServiceMock->expects($this->once())
			->method('deleteSingleNode')
			->with($node);

		$this->nestedSetServiceMock->expects($this->never())
			->method('deleteTree');

		$result = $this->nodesService->deleteNode($nodeId);
		$this->assertEquals(1, $result);
	}

	/**
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteFailsByNode(): void
	{
		$nodeId = 1;
		$this->nodesService->UID = 34;

		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($nodeId)
			->willReturn([]);

		$this->aclValidatorMock->expects($this->never())
			->method('checkDirectoryPermissions');
		$this->nestedSetServiceMock->expects($this->never())
			->method('findAllSubNodeIdsByRootIdsAndPosition');
		$this->nestedSetServiceMock->expects($this->never())
			->method('deleteSingleNode');
		$this->nestedSetServiceMock->expects($this->never())
			->method('deleteTree');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Can not find a node for node_id ' . $nodeId);

		$this->nodesService->deleteNode($nodeId);
	}

	/**
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteFailsByRights(): void
	{
		$nodeId = 1;
		$this->nodesService->UID = 34;
		$node = ['node_id' => 1, 'name' => 'Node to Delete', 'parent_id' => 12, 'children' => 0,
			'UID' => 123, 'visibility' => 1, 'root_id' => 1, 'rgt' => 2, 'lft' => 1];
		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($nodeId)
			->willReturn($node);

		$rights = ['create' => false, 'read' => true, 'edit' => false, 'share' => ''];
		$this->aclValidatorMock->expects($this->once())
			->method('checkDirectoryPermissions')
			->willReturn($rights);

		$this->nestedSetServiceMock->expects($this->never())
			->method('findAllSubNodeIdsByRootIdsAndPosition');
		$this->nestedSetServiceMock->expects($this->never())
			->method('deleteSingleNode');
		$this->nestedSetServiceMock->expects($this->never())
			->method('deleteTree');

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('No rights to delete node ' . $nodeId);

		$this->nodesService->deleteNode($nodeId);
	}

	/**
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteNodeTreeSucceed(): void
	{
		$nodeId = 1;
		$this->nodesService->UID = 354;
		$node = ['node_id' => 1, 'name' => 'Node to Delete', 'parent_id' => 12, 'children' => 3,
			'UID' => 123, 'visibility' => 1, 'root_id' => 1, 'rgt' => 2, 'lft' => 1];
		$this->nodesRepositoryMock->expects($this->once())
			->method('getNode')
			->with($nodeId)
			->willReturn($node);

		$rights = ['create' => true, 'read' => true, 'edit' => true, 'share' => 'global'];
		$this->aclValidatorMock->expects($this->once())
			->method('checkDirectoryPermissions')
			->willReturn($rights);

		$this->nestedSetServiceMock->expects($this->once())
			->method('findAllSubNodeIdsByRootIdsAndPosition')
			->with($node['root_id'], $node['rgt'], $node['lft'])
			->willReturn([['heidewitzka'], ['der'], ['Kapitän']]);

		$this->nestedSetServiceMock->expects($this->never())
			->method('deleteSingleNode');

		$this->nestedSetServiceMock->expects($this->once())
			->method('deleteTree')
			->with($node);

		$result = $this->nodesService->deleteNode($nodeId);
		$this->assertEquals(3, $result);
	}

}