<?php

namespace Tests\Unit\Framework\Database\NestedSets;

use App\Framework\Database\BaseRepositories\NestedSetHelper;
use App\Framework\Database\NestedSets\Calculator;
use App\Framework\Exceptions\DatabaseException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
	private Calculator $calculator;
	
	protected function setUp(): void
	{
		$this->calculator = new Calculator();
	}
	
	/**
	 * @throws DatabaseException
	 */
	#[Group('units')]
	public function testDetermineLgtPositionByRegionWithValidRegions(): void
	{
		$node = ['lft' => 5, 'rgt' => 10];

		$this->assertSame(5, $this->calculator->determineLgtPositionByRegion(Calculator::REGION_BEFORE, $node));
		$this->assertSame(10, $this->calculator->determineLgtPositionByRegion(Calculator::REGION_APPENDCHILD, $node));
		$this->assertSame(11, $this->calculator->determineLgtPositionByRegion(Calculator::REGION_AFTER, $node));
	}

	/**
	 * @throws DatabaseException
	 */
	#[Group('units')]
	public function testDetermineLgtPositionByRegionThrowsExceptionForInvalidRegion(): void
	{
		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Unknown region: invalid');

		$this->calculator->determineLgtPositionByRegion('invalid', ['lft' => 5, 'rgt' => 10]);
	}


	#[Group('units')]
	public function testCalculateDiffLevelByRegionForBeforeRegion(): void
	{
		$region = Calculator::REGION_BEFORE;
		$movedLevel = 3;
		$targetLevel = 5;

		$expectedDiffLevel = $targetLevel - $movedLevel;

		$this->assertSame($expectedDiffLevel, $this->calculator->calculateDiffLevelByRegion($region, $movedLevel, $targetLevel));
	}

	#[Group('units')]
	public function testCalculateDiffLevelByRegionWhenMovedAndTargetLevelsAreEqual(): void
	{
		$region = Calculator::REGION_BEFORE;
		$movedLevel = 6;
		$targetLevel = 6;

		$expectedDiffLevel = 0;

		$this->assertSame($expectedDiffLevel, $this->calculator->calculateDiffLevelByRegion($region, $movedLevel, $targetLevel));
	}

	#[Group('units')]
	public function testCalculateDiffLevelByRegionForNegativeMovedLevel(): void
	{
		$region = Calculator::REGION_AFTER;
		$movedLevel = -2;
		$targetLevel = 3;

		$expectedDiffLevel = $targetLevel - $movedLevel;

		$this->assertSame($expectedDiffLevel, $this->calculator->calculateDiffLevelByRegion($region, $movedLevel, $targetLevel));
	}

	#[Group('units')]
	public function testCalculateDiffLevelByRegionForAppendChildWithHighLevelDifference(): void
	{
		$region = Calculator::REGION_APPENDCHILD;
		$movedLevel = 1;
		$targetLevel = 1000;

		$expectedDiffLevel = ($targetLevel - $movedLevel) + 1;

		$this->assertSame($expectedDiffLevel, $this->calculator->calculateDiffLevelByRegion($region, $movedLevel, $targetLevel));
	}

	#[Group('units')]
	public function testCalculateDiffLevelByRegionForAfterRegion(): void
	{
		$region = Calculator::REGION_AFTER;
		$movedLevel = 3;
		$targetLevel = 7;

		$expectedDiffLevel = $targetLevel - $movedLevel;

		$this->assertSame($expectedDiffLevel, $this->calculator->calculateDiffLevelByRegion($region, $movedLevel, $targetLevel));
	}

	#[Group('units')]
	public function testCalculateDiffLevelByRegionForAppendChildRegion(): void
	{
		$region = Calculator::REGION_APPENDCHILD;
		$movedLevel = 2;
		$targetLevel = 4;

		$expectedDiffLevel = ($targetLevel - $movedLevel) + 1;

		$this->assertSame($expectedDiffLevel, $this->calculator->calculateDiffLevelByRegion($region, $movedLevel, $targetLevel));
	}

	#[Group('units')]
	public function testDetermineParentIdByRegionForAppendChild(): void
	{
		$node = ['node_id' => 10, 'parent_id' => 5];
		$region = NestedSetHelper::REGION_APPENDCHILD;

		$this->assertSame(10, $this->calculator->determineParentIdByRegion($region, $node));
	}

	#[Group('units')]
	public function testDetermineParentIdByRegionForNonAppendChild(): void
	{
		$node = ['node_id' => 10, 'parent_id' => 5];
		$region = NestedSetHelper::REGION_BEFORE;

		$this->assertSame(5, $this->calculator->determineParentIdByRegion($region, $node));
	}

	#[Group('units')]
	public function testCalculateBeforeMoveSubTreeWithPositiveDistance(): void
	{
		$movedNode = ['lft' => 3, 'root_id' => 1];
		$targetNode = ['lft' => 7, 'root_id' => 1];
		$newLgtPos = 10;
		$width = 4;

		$result = $this->calculator->calculateBeforeMoveSubTree($movedNode, $targetNode, $newLgtPos, $width);

		$this->assertSame(['distance' => 7, 'tmpPos' => 3, 'width' => 4], $result);
	}

	#[Group('units')]
	public function testCalculateBeforeMoveSubTreeWithNegativeDistanceSameRoot(): void
	{
		$movedNode = ['lft' => 10, 'root_id' => 1];
		$targetNode = ['lft' => 3, 'root_id' => 1];
		$newLgtPos = 5;
		$width = 4;

		$result = $this->calculator->calculateBeforeMoveSubTree($movedNode, $targetNode, $newLgtPos, $width);

		$this->assertSame(['distance' => -9, 'tmpPos' => 14, 'width' => 4], $result);
	}

	#[Group('units')]
	public function testCalculateBeforeMoveSubTreeWithNegativeDistanceDifferentRoot(): void
	{
		$movedNode = ['lft' => 8, 'root_id' => 1];
		$targetNode = ['lft' => 5, 'root_id' => 2];
		$newLgtPos = 4;
		$width = 5;

		$result = $this->calculator->calculateBeforeMoveSubTree($movedNode, $targetNode, $newLgtPos, $width);

		$this->assertSame(['distance' => -4, 'tmpPos' => 8, 'width' => 5], $result);
	}

	#[Group('units')]
	public function testCalculateBeforeMoveSubTreeWithZeroDistance(): void
	{
		$movedNode = ['lft' => 3, 'root_id' => 1];
		$targetNode = ['lft' => 3, 'root_id' => 1];
		$newLgtPos = 3;
		$width = 2;

		$result = $this->calculator->calculateBeforeMoveSubTree($movedNode, $targetNode, $newLgtPos, $width);

		$this->assertSame(['distance' => 0, 'tmpPos' => 3, 'width' => 2], $result);
	}
}
