<?php

namespace Tests\Unit\Framework\Database\BaseRepositories;

use App\Framework\Database\BaseRepositories\NestedSetHelper;
use App\Framework\Exceptions\DatabaseException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NestedSetHelperTest extends TestCase
{
	private QueryBuilder&MockObject $queryBuilderMock;
	private NestedSetHelper $nestedSetHelper;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$connectionMock = $this->createMock(Connection::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);

		$connectionMock->method('createQueryBuilder')->willReturn($this->queryBuilderMock);

		$this->nestedSetHelper = new NestedSetHelper();
		$this->nestedSetHelper->init($connectionMock, 'table');
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

	/**
	 * @throws DatabaseException
	 */
	#[Group('units')]
	public function testDetermineLgtPositionByRegionWithValidRegions(): void
	{
		$node = ['lft' => 5, 'rgt' => 10];

		$this->assertSame(5, $this->nestedSetHelper->determineLgtPositionByRegion(NestedSetHelper::REGION_BEFORE, $node));
		$this->assertSame(10, $this->nestedSetHelper->determineLgtPositionByRegion(NestedSetHelper::REGION_APPENDCHILD, $node));
		$this->assertSame(11, $this->nestedSetHelper->determineLgtPositionByRegion(NestedSetHelper::REGION_AFTER, $node));
	}

	/**
	 * @throws DatabaseException
	 */
	#[Group('units')]
	public function testDetermineLgtPositionByRegionThrowsExceptionForInvalidRegion(): void
	{
		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Unknown region: invalid');

		$this->nestedSetHelper->determineLgtPositionByRegion('invalid', ['lft' => 5, 'rgt' => 10]);
	}

	#[Group('units')]
	public function testCalculateDiffLevelByRegionForBeforeRegion(): void
	{
		$region = NestedSetHelper::REGION_BEFORE;
		$movedLevel = 3;
		$targetLevel = 5;

		$expectedDiffLevel = $targetLevel - $movedLevel;

		$this->assertSame($expectedDiffLevel, $this->nestedSetHelper->calculateDiffLevelByRegion($region, $movedLevel, $targetLevel));
	}

	#[Group('units')]
	public function testCalculateDiffLevelByRegionForAfterRegion(): void
	{
		$region = NestedSetHelper::REGION_AFTER;
		$movedLevel = 3;
		$targetLevel = 7;

		$expectedDiffLevel = $targetLevel - $movedLevel;

		$this->assertSame($expectedDiffLevel, $this->nestedSetHelper->calculateDiffLevelByRegion($region, $movedLevel, $targetLevel));
	}

	#[Group('units')]
	public function testCalculateDiffLevelByRegionForAppendChildRegion(): void
	{
		$region = NestedSetHelper::REGION_APPENDCHILD;
		$movedLevel = 2;
		$targetLevel = 4;

		$expectedDiffLevel = ($targetLevel - $movedLevel) + 1;

		$this->assertSame($expectedDiffLevel, $this->nestedSetHelper->calculateDiffLevelByRegion($region, $movedLevel, $targetLevel));
	}

	#[Group('units')]
	public function testDetermineParentIdByRegionForAppendChild(): void
	{
		$node = ['node_id' => 10, 'parent_id' => 5];
		$region = NestedSetHelper::REGION_APPENDCHILD;

		$this->assertSame(10, $this->nestedSetHelper->determineParentIdByRegion($region, $node));
	}

	#[Group('units')]
	public function testDetermineParentIdByRegionForNonAppendChild(): void
	{
		$node = ['node_id' => 10, 'parent_id' => 5];
		$region = NestedSetHelper::REGION_BEFORE;

		$this->assertSame(5, $this->nestedSetHelper->determineParentIdByRegion($region, $node));
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
	public function testMoveSubTreeWithDistance(): void
	{
		$movedNode  = ['node_id' => 2, 'lft' => 6, 'rgt' => 7, 'root_id' => 1, 'parent_id' => 1, 'level' => 3];
		$targetNode = ['node_id' => 3, 'lft' => 1, 'rgt' => 2, 'root_id' => 1, 'parent_id' => 2, 'level' => 1];
		$width = 1;
		$newLgtPos = 1;
		$diffLevel = 2;

		// calculated values
		$distance = -6;
		$tmpPos = 7;

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


}
