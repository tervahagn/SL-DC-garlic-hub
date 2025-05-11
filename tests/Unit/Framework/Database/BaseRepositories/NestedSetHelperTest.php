<?php

namespace Tests\Unit\Framework\Database\BaseRepositories;

use App\Framework\Database\BaseRepositories\NestedSetHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class NestedSetHelperTest extends TestCase
{
	private readonly Connection $connectionMock;
	private QueryBuilder $queryBuilderMock;
	private Result $resultMock;
	private NestedSetHelper $nestedSetHelper;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->connectionMock   = $this->createMock(Connection::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);
		$this->resultMock       = $this->createMock(Result::class);

		$this->connectionMock->method('createQueryBuilder')->willReturn($this->queryBuilderMock);

		$this->nestedSetHelper = new NestedSetHelper();
		$this->nestedSetHelper->init($this->connectionMock, 'table');
	}


	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testMoveSubTree(): void
	{
		$movedNode  = ['node_id' => 2, 'lft' => 1, 'rgt' => 2, 'root_id' => 1, 'parent_id' => 1, 'level' => 1];
		$targetNode = ['node_id' => 3, 'lft' => 1, 'rgt' => 2, 'root_id' => 2, 'parent_id' => 2, 'level' => 1];
		$width = 1;
		$newLgtPos = 1;
		$diffLevel = 0;

		// calculated values
		$distance = 0;
		$tmpPos = 1;

		$this->queryBuilderMock->expects($this->once())->method('update')
			->with('table')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->exactly(4))->method('set')
			->willReturnMap([
				['lft', 'lft + :distance', $this->queryBuilderMock],
				['rgt', 'rgt + :distance', $this->queryBuilderMock],
				['level', 'level + :diff_level', $this->queryBuilderMock],
				['root_id', ':target_root_id', $this->queryBuilderMock]
			]);
		$this->queryBuilderMock->expects($this->once())->method('where')
			->with('root_id = :moved_root_id')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->exactly(2))->method('andWhere')
			->willReturnMap([
				['lft >= :tmpPos', $this->queryBuilderMock],
				['rgt < :tmpPos + :width', $this->queryBuilderMock]
			]);
		$this->queryBuilderMock->expects($this->exactly(6))->method('setParameter')
			->willReturnMap([
				['distance', $distance, $this->queryBuilderMock],
				['diff_level', $diffLevel, $this->queryBuilderMock],
				['target_root_id', $targetNode['root_id'], $this->queryBuilderMock],
				['moved_root_id', $movedNode['root_id'], $this->queryBuilderMock],
				['tmpPos', $tmpPos, $this->queryBuilderMock],
				['width', $width, $this->queryBuilderMock]
			]);

		$this->queryBuilderMock->expects($this->once())->method('executeStatement')->willReturn(1);


		$this->assertSame(1, $this->nestedSetHelper->moveSubTree($movedNode, $targetNode, $newLgtPos,$width, $diffLevel));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testMoveNodesToRightForInsert(): void
	{
		$rootId = 2;
		$width = 6;
		$position = 3;

		$this->queryBuilderMock->expects($this->once())->method('update')
			->with('table')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('set')
			->with('lft', 'lft + :steps')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('where')
			->with('root_id = :root_id')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('andWhere')
			->with('lft >= :position')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->exactly(3))->method('setParameter')
			->willReturnMap([
				['steps', $width, $this->queryBuilderMock],
				['root_id', $rootId, $this->queryBuilderMock],
				['position', $position, $this->queryBuilderMock]
			]);
		$this->queryBuilderMock->expects($this->once())->method('executeStatement')->willReturn(1);

		$this->assertSame(1, $this->nestedSetHelper->moveNodesToRightForInsert($rootId, $position, $width));

	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testMoveNodesToLeftForInsert(): void
	{
		$rootId = 2;
		$width = 6;
		$position = 3;

		$this->queryBuilderMock->expects($this->once())->method('update')
			->with('table')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('set')
			->with('rgt', 'rgt + :steps')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('where')
			->with('root_id = :root_id')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('andWhere')
			->with('rgt >= :position')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->exactly(3))->method('setParameter')
			->willReturnMap([
				['steps', $width, $this->queryBuilderMock],
				['root_id', $rootId, $this->queryBuilderMock],
				['position', $position, $this->queryBuilderMock]
			]);
		$this->queryBuilderMock->expects($this->once())->method('executeStatement')->willReturn(1);

		$this->assertSame(1, $this->nestedSetHelper->moveNodesToLeftForInsert($rootId, $position, $width));
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testMoveNodesToLeftForDeletion(): void
	{
		$rootId = 2;
		$width = 6;
		$position = 3;

		$this->queryBuilderMock->expects($this->once())->method('update')
			->with('table')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('set')
			->with('lft', 'lft - '.$width)
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('where')
			->with('root_id = :root_id')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('andWhere')
			->with('lft > :position')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->exactly(2))->method('setParameter')
			->willReturnMap([
				['root_id', $rootId, $this->queryBuilderMock],
				['position', $position, $this->queryBuilderMock]
			]);
		$this->queryBuilderMock->expects($this->once())->method('executeStatement')->willReturn(1);

		$this->assertSame(1, $this->nestedSetHelper->moveNodesToLeftForDeletion($rootId, $position, $width));
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testMoveNodesToRightForDeletionMock()
	{
		$rootId = 1;
		$width  = 6;
		$position = 3;

		$this->queryBuilderMock->expects($this->once())->method('update')
			->with('table')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('set')
			->with('rgt', 'rgt - '.$width)
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('where')
			->with('root_id = :root_id')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('andWhere')
			->with('rgt > :position')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->exactly(2))->method('setParameter')
			->willReturnMap([
				['root_id', $rootId, $this->queryBuilderMock],
				['position', $position, $this->queryBuilderMock]
			]);
		$this->queryBuilderMock->expects($this->once())->method('executeStatement')->willReturn(1);

		$this->assertSame(1, $this->nestedSetHelper->moveNodesToRightForDeletion($rootId, $position, $width));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteFullTree(): void
	{
		$node = ['root_id' => 1, 'rgt' => 6, 'lft' => 3];

		$this->queryBuilderMock->expects($this->once())->method('delete')
			->with('table')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('where')
			->with('root_id = :root_id')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('andWhere')
			->with('lft between :pos_lft AND :pos_rgt')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->exactly(3))->method('setParameter')
			->willReturnMap([
				['root_id', $node['root_id'], $this->queryBuilderMock],
				['pos_lft', $node['lft'], $this->queryBuilderMock],
				['pos_rgt', $node['rgt'], $this->queryBuilderMock]
			]);
		$this->queryBuilderMock->expects($this->once())->method('executeStatement')->willReturn(1);

		$this->assertSame(1, $this->nestedSetHelper->deleteFullTree($node['root_id'], $node['rgt'], $node['lft']));
	}

}
