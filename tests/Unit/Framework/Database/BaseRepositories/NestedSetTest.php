<?php

namespace Tests\Unit\Framework\Database\BaseRepositories;

use App\Framework\Database\BaseRepositories\NestedSet;
use App\Framework\Database\BaseRepositories\NestedSetHelper;
use App\Framework\Exceptions\DatabaseException;
use App\Framework\Exceptions\FrameworkException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class NestedSetTest extends TestCase
{
	private Connection&MockObject $connectionMock;
	private QueryBuilder&MockObject $queryBuilderMock;
	private Result&MockObject $resultMock;
	private NestedSetHelper&MockObject $helperMock;
	private LoggerInterface&MockObject $loggerMock;
	private NestedSet $repository;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->connectionMock   = $this->createMock(Connection::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);
		$this->resultMock       = $this->createMock(Result::class);
		$this->helperMock       = $this->createMock(NestedSetHelper::class);
		$this->loggerMock       = $this->createMock(LoggerInterface::class);

		$this->repository       = new NestedSet($this->connectionMock, $this->helperMock, $this->loggerMock, 'table', 'id');
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllRootNodes(): void
	{
		$expectedResult = [
			[
				'media_id' => 1,
				'parent_id' => 0,
				'username' => 'testuser',
				'children' => 2
			],
			[
				'media_id' => 2,
				'parent_id' => 0,
				'username' => 'another_user',
				'children' => 0
			]
		];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')
			->with('*, FLOOR((rgt-lft)/2) AS children')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())->method('from')
			->with('table')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())->method('leftJoin')
			->with('table', 'user_main', 'user_main', 'table.UID = user_main.UID')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())->method('where')
			->with('parent_id = 0')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())->method('orderBy')
			->with('root_order', 'ASC')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn($expectedResult);

		$actualResult = $this->repository->findAllRootNodes();
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindTreeByRootId(): void
	{
		$rootId = 1;
		$expectedResult = [
			['children' => 2, 'node_id' => 1, 'name' => 'Root Node', 'company_id' => 1],
			['children' => 0, 'node_id' => 2, 'name' => 'Child Node 1', 'company_id' => 1],
			['children' => 0, 'node_id' => 3, 'name' => 'Child Node 2', 'company_id' => 2],
		];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		// ... (Mock query builder methods for select, from, leftJoin, where, setParameter, groupBy)

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn($expectedResult);

		$actualResult = $this->repository->findTreeByRootId($rootId);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception|DatabaseException
	 */
	#[Group('units')]
	public function testFindNodeOwner(): void
	{
		$nodeId = 2;
		$expectedResult = ['UID' => 1, 'node_id' => 2, 'name' => 'Child Node 1', 'company_id' => 1];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		// ... (Mock query builder methods)

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAssociative')
			->willReturn($expectedResult);

		$actualResult = $this->repository->findNodeOwner($nodeId);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindNodeOwnerNotFound(): void
	{
		$nodeId = 999; // Non-existent node ID

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		// ... (Mock query builder methods)

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAssociative')
			->willReturn(false); // Simulate no result

		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Node not found');

		$this->repository->findNodeOwner($nodeId);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllChildNodesByParentNode(): void
	{
		$parentId = 1;
		$expectedResult = [
			['node_id' => 2, 'name' => 'Child Node 1', 'children' => 0],
			['node_id' => 3, 'name' => 'Child Node 2', 'children' => 0],
		];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		// ... (Mock query builder methods)

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn($expectedResult);

		$actualResult = $this->repository->findAllChildNodesByParentNode($parentId);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception|Exception
	 */
	#[Group('units')]
	public function testFindAllChildrenInTreeOfNodeId(): void
	{
		$nodeId = 2;
		$nodeData = [['root_id' => 1, 'rgt' => 6, 'lft' => 3]];
		$expectedResult = [
			['node_id' => 2, 'category_name' => 'Child Node 1'],
			['node_id' => 3, 'category_name' => 'Child Node 2'],
		];

		$queryBuilderMock2 = $this->createMock(QueryBuilder::class);

		$this->connectionMock->expects($this->exactly(2))->method('createQueryBuilder')
			->willReturnOnConsecutiveCalls($this->queryBuilderMock, $queryBuilderMock2);

		$this->queryBuilderMock->expects($this->once())->method('select')
			->with('root_id, rgt, lft')
			->willReturnSelf();
		// ... other query builder method calls
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);
		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn($nodeData);


		// Second query (find children)
		$queryBuilderMock2->expects($this->once())->method('select')
			->with('node_id, category_name')
			->willReturnSelf();

		$resultMock2 = $this->createMock(Result::class);

		$queryBuilderMock2->expects($this->once())->method('executeQuery')
			->willReturn($resultMock2);
		$resultMock2->expects($this->once())->method('fetchAllAssociative')
			->willReturn($expectedResult);

		$actualResult = $this->repository->findAllChildrenInTreeOfNodeId($nodeId);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllChildrenInTreeOfNodeIdNoNodeData(): void
	{
		$nodeId = 999; // Non-existent node

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		// ... (Mock query builder methods)

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn([]); // Simulate empty result

		$actualResult = $this->repository->findAllChildrenInTreeOfNodeId($nodeId);
		$this->assertEquals([], $actualResult); // Expect an empty array
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindRootIdRgtAndLevelByNodeId(): void
	{
		$nodeId = 2;
		$expectedResult = ['root_id' => 1, 'rgt' => 6, 'lft' => 3];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')
			->with('root_id, rgt, lft')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('from')
			->with('table')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())
			->method('where')
			->with('node_id = :node_id')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('setParameter')
			->with('node_id', $nodeId)
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAssociative')
			->willReturn($expectedResult);

		$actualResult = $this->repository->findRootIdRgtAndLevelByNodeId($nodeId);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllSubNodeIdsByRootIdsAndPosition(): void
	{
		$rootId = 1;
		$nodeRgt = 6;
		$nodeLft = 3;
		$expectedResult = [
			['node_id' => 2],
			['node_id' => 3],
		];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')
			->with('node_id')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())->method('from')
			->with('table')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())->method('where')
			->with('root_id = :root_id')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->exactly(2))->method('andWhere')
			->willReturnCallback(function ($condition) {
				$expectedConditions = ['lft >= :node_lft', 'rgt <= :node_rgt'];
				$this->assertContains($condition, $expectedConditions);
				return $this->queryBuilderMock;
			});

		$this->queryBuilderMock->expects($this->exactly(3))->method('setParameter')
			->willReturnCallback(function ($name, $value) {
				$expectedNames = ['root_id', 'node_lft', 'node_rgt'];
				$expectedValues = [1, 6, 3];
				$this->assertContains($name, $expectedNames);
				$this->assertContains($value, $expectedValues);
				return $this->queryBuilderMock;
			});


		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn($expectedResult);

		$actualResult = $this->repository->findAllSubNodeIdsByRootIdsAndPosition($rootId, $nodeRgt, $nodeLft);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 * @throws DatabaseException
	 */
	#[Group('units')]
	public function testAddRootNode(): void
	{
		$uid = 1;
		$name = 'Test Root';
		$expectedNewNodeId = 5; // Example ID - adjust as needed

		$this->connectionMock->expects($this->once())->method('beginTransaction');

		$this->connectionMock->expects($this->once())->method('insert')
			->with('table', [
				'name' => $name,
				'parent_id' => 0,
				'root_order' => 0,
				'visibility' => 0,
				'lft' => 1,
				'rgt' => 2,
				'UID' => $uid,
				'level' => 1
			]);

		$this->connectionMock->expects($this->once())->method('lastInsertId')
			->willReturn($expectedNewNodeId);

		$updateFields = ['root_id' => $expectedNewNodeId, 'root_order' => $expectedNewNodeId];
		$this->connectionMock->expects($this->once())->method('update')
			->with('table', $updateFields,  ['id' => $expectedNewNodeId])
			->willReturn(1);

		$this->connectionMock->expects($this->once())->method('commit');

		$actualNewNodeId = $this->repository->addRootNodeSecured($uid, $name);
		$this->assertEquals($expectedNewNodeId, $actualNewNodeId);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testAddRootNodeInsertFails(): void
	{
		$uid = 1;
		$name = 'Test Root';

		$this->connectionMock->expects($this->once())->method('beginTransaction');

		$this->connectionMock->expects($this->once())->method('insert')
			->willReturn(0); // Simulate insert failure

		$this->connectionMock->expects($this->once())->method('rollback');

		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Add root node failed because of: Insert new node failed');

		$this->repository->addRootNodeSecured($uid, $name);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception|DatabaseException
	 */
	#[Group('units')]
	public function testAddRootNodeUpdateFails(): void
	{
		$uid = 1;
		$name = 'Test Root';

		$this->connectionMock->expects($this->once())->method('beginTransaction');

		$this->connectionMock->expects($this->once())->method('insert');

		$this->connectionMock->expects($this->once())->method('lastInsertId')
			->willReturn(1);

		$this->connectionMock->expects($this->once())->method('update')
			->willReturn(0); // simulate no update

		$this->connectionMock->expects($this->once())->method('rollback');

		$this->expectException(\Exception::class); // Or DatabaseException, depending on your exception hierarchy
		$this->expectExceptionMessage('Update root node failed');

		$this->repository->addRootNodeSecured($uid, $name);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception|DatabaseException
	 */
	#[Group('units')]
	public function testAddSubNode(): void
	{
		$uid = 2;
		$name = 'Test Sub Node';
		$parentNode = ['rgt' => 5, 'node_id' => 1, 'root_id' => 1, 'level' => 1];
		$expectedNewNodeId = 7; // Example ID

		$this->connectionMock->expects($this->once())->method('beginTransaction');

		// moveNodesToLeftForInsert

		// moveNodesToRightForInsert

		$this->connectionMock->expects($this->once())->method('insert');

		$this->connectionMock->expects($this->once())->method('lastInsertId')
			->willReturn($expectedNewNodeId);

		$this->connectionMock->expects($this->once())->method('commit');

		$actualNewNodeId = $this->repository->addSubNode($uid, $name, $parentNode);
		$this->assertEquals($expectedNewNodeId, $actualNewNodeId);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testAddSubNodeInsertFails(): void
	{
		$uid = 2;
		$name = 'Test Sub Node';
		$parentNode = ['rgt' => 5, 'node_id' => 1, 'root_id' => 1, 'level' => 1];

		$this->connectionMock->expects($this->once())->method('beginTransaction');

		// moveNodesToLeftForInsert

		// moveNodesToRightForInsert

		$this->connectionMock->expects($this->once())->method('insert');

		$this->connectionMock->expects($this->once())->method('lastInsertId')
			->willReturn(0);// Simulate insert failure

		$this->connectionMock->expects($this->once())->method('rollBack'); // Corrected method name

		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Add sub node failed because of: Insert new sub node failed');

		$this->repository->addSubNode($uid, $name, $parentNode);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 * @throws DatabaseException
	 */
	#[Group('units')]
	public function testDeleteSingleNode(): void
	{
		$node = ['node_id' => 2, 'root_id' => 1, 'rgt' => 5];

		$this->connectionMock->expects($this->once())->method('beginTransaction');

		$this->connectionMock->expects($this->once())->method('delete')
			->willReturn(1); // Simulate successful deletion

		// moveNodesToLeftForDeletion

		// moveNodesToRightForDeletion

		$this->connectionMock->expects($this->once())->method('commit');

		$this->repository->deleteSingleNode($node);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteSingleNodeDeleteFails(): void
	{
		$node = ['node_id' => 2, 'root_id' => 1, 'rgt' => 5];

		$this->connectionMock->expects($this->once())
			->method('beginTransaction');

		$this->connectionMock->expects($this->once())
			->method('delete')
			->willReturn(0); // Simulate delete failure

		$this->connectionMock->expects($this->once())
			->method('rollBack');

		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('delete single node failed because of: not exists');

		$this->repository->deleteSingleNode($node);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception|DatabaseException
	 * @throws Exception
	 */
	#[Group('units')]
	public function testDeleteTree(): void
	{
		$node = ['root_id' => 1, 'rgt' => 6, 'lft' => 3];
		$move = 4.0;
		$this->connectionMock->expects($this->once())->method('beginTransaction');

		$this->helperMock->expects($this->once())->method('deleteFullTree')
			->with(1,6,3)
			->willReturn(1);

		$this->helperMock->expects($this->once())->method('moveNodesToLeftForDeletion')
			->with($node['root_id'], $node['rgt'], $move)
			->willReturn(1);

		$this->helperMock->expects($this->once())->method('moveNodesToRightForDeletion')
			->with($node['root_id'], $node['rgt'], $move)
			->willReturn(1);

		$this->connectionMock->expects($this->once())->method('commit');

		$this->repository->deleteTree($node);
	}

	/**
	 * @throws DatabaseException
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteTreeFails(): void
	{
		$node = ['root_id' => 1, 'rgt' => 6, 'lft' => 3];
		$this->connectionMock->expects($this->once())->method('beginTransaction');

		$this->helperMock->expects($this->once())->method('deleteFullTree')
			->with(1,6,3)
			->willReturn(0);
		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Delete tree failed because of: not exists');
		$this->connectionMock->expects($this->once())->method('rollBack');

		$this->repository->deleteTree($node);
	}

	/**
	 * @throws DatabaseException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws FrameworkException
	 * @throws Exception
	 */
	#[Group('units')]
	public function testMoveNodeBefore(): void
	{
		$movedNode = ['node_id' => 2, 'lft' => 3, 'rgt' => 6, 'root_id' => 1, 'parent_id' => 1, 'level' => 2];
		$targetNode = ['node_id' => 3, 'lft' => 7, 'rgt' => 8, 'root_id' => 1, 'parent_id' => 1, 'level' => 2];
		$region = 'before';
		$width = 4.0;

		$this->connectionMock->expects($this->once())->method('beginTransaction');

		$this->helperMock->expects($this->once())->method('moveNodesToRightForInsert')
			->with($movedNode['root_id'], 0, $width)
			->willReturn(1);

		$this->helperMock->expects($this->once())->method('moveNodesToLeftForInsert')
			->with($movedNode['root_id'], 0, $width)
			->willReturn(1);

		$this->helperMock->expects($this->once())->method('moveSubTree')
			->with($movedNode, $targetNode, 0, $width, 0)
			->willReturn(1);

		$this->connectionMock->expects($this->once())->method('update')
			->with('table', ['parent_id' => 0], ['id' => 2]);

		$this->helperMock->expects($this->once())->method('moveNodesToLeftForDeletion')
			->with($movedNode['root_id'], $movedNode['rgt'], $width)
			->willReturn(1);

		$this->helperMock->expects($this->once())->method('moveNodesToRightForDeletion')
			->with($movedNode['root_id'], $movedNode['rgt'], $width)
			->willReturn(1);

		$this->connectionMock->expects($this->once())->method('commit');

		$this->repository->moveNode($movedNode, $targetNode, $region);
	}

	/**
	 * @throws DatabaseException
	 * @throws FrameworkException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testMoveNodeAfter(): void
	{
		$movedNode = ['node_id' => 2, 'lft' => 3, 'rgt' => 6, 'root_id' => 1, 'parent_id' => 1, 'level' => 2];
		$targetNode = ['node_id' => 3, 'lft' => 7, 'rgt' => 8, 'root_id' => 1, 'parent_id' => 1, 'level' => 2];
		$region = 'after';
		$width = 4;

		$this->connectionMock->expects($this->once())->method('beginTransaction');

		$this->helperMock->expects($this->once())->method('moveNodesToRightForInsert')
			->with($movedNode['root_id'], 0, $width)
			->willReturn(1);

		$this->helperMock->expects($this->once())->method('moveNodesToLeftForInsert')
			->with($movedNode['root_id'], 0, $width)
			->willReturn(1);

		$this->helperMock->expects($this->once())->method('moveSubTree')
			->with($movedNode, $targetNode, 0, $width, 0)
			->willReturn(1);

		$this->connectionMock->expects($this->once())->method('update')
			->with('table', ['parent_id' => 0], ['id' => 2]);

		$this->helperMock->expects($this->once())->method('moveNodesToLeftForDeletion')
			->with($movedNode['root_id'], $movedNode['rgt'], $width)
			->willReturn(1);

		$this->helperMock->expects($this->once())->method('moveNodesToRightForDeletion')
			->with($movedNode['root_id'], $movedNode['rgt'], $width)
			->willReturn(1);


		$this->connectionMock->expects($this->once())->method('commit');

		$this->repository->moveNode($movedNode, $targetNode, $region);
	}


	/**
	 * @throws DatabaseException
	 * @throws FrameworkException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testMoveNodeFailsOnMoveSubTree(): void
	{
		$movedNode = ['node_id' => 2, 'name' => 'moved', 'lft' => 3, 'rgt' => 6, 'root_id' => 1, 'parent_id' => 1, 'level' => 2];
		$targetNode = ['node_id' => 3, 'name' => 'target', 'lft' => 7, 'rgt' => 8, 'root_id' => 1, 'parent_id' => 1, 'level' => 2];
		$region = 'after';
		$width = 4;

		$this->connectionMock->expects($this->once())->method('beginTransaction');

		$this->helperMock->expects($this->once())->method('moveNodesToRightForInsert')
			->with($movedNode['root_id'], 0, $width)
			->willReturn(1);

		$this->helperMock->expects($this->once())->method('moveNodesToLeftForInsert')
			->with($movedNode['root_id'], 0, $width)
			->willReturn(1);

		$this->helperMock->expects($this->once())->method('moveSubTree')
			->with($movedNode, $targetNode, 0, $width, 0)
			->willReturn(0);

		$this->connectionMock->expects($this->never())->method('update');
		$this->helperMock->expects($this->never())->method('moveNodesToLeftForDeletion');
		$this->helperMock->expects($this->never())->method('moveNodesToRightForDeletion');
		$this->connectionMock->expects($this->never())->method('commit');

		$this->connectionMock->expects($this->once())->method('rollBack');
		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Moving nodes failed: '.$movedNode['name'].' cannot be moved via '.$region.' of '. $targetNode['name']);

		$this->repository->moveNode($movedNode, $targetNode, $region);
	}

}
