<?php

namespace Tests\Unit\Modules\Playlists\Helper\ExportSmil\Utils;

use App\Modules\Playlists\Helper\ExportSmil\Utils\Conditional;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ConditionalTest extends TestCase
{
	private Conditional $conditionalMock;

	protected function setUp(): void
	{
		$this->conditionalMock = new Conditional([]);
	}

	#[Group('units')]
	public function testDetermineExprAttributeReturnsEmptyWhenNoConditional(): void
	{
		$this->conditionalMock = new Conditional([]);
		$result = $this->conditionalMock->determineExprAttribute();

		$this->assertSame('', $result);
	}

	#[Group('units')]
	public function testDetermineExprAttributeWithDateConditions(): void
	{
		$conditions = [
			'date_from' => '2023-11-01',
			'date_until' => '2023-12-31',
			'time_from' => '00:00:00',
			'time_until' => '00:00:00',
			'weektimes' => []
		];

		$this->conditionalMock = new Conditional($conditions);
		$result = $this->conditionalMock->determineExprAttribute();

		$expected = 'expr="adapi-compare(substring-before(adapi-date(), \'T\'), \'2023-11-01\')&gt;=0 and adapi-compare(substring-before(adapi-date(), \'T\'), \'2023-12-31\')&lt;=0" ';
		$this->assertSame($expected, $result);
	}

	#[Group('units')]
	public function testDetermineExprAttributeWithDateTimeConditions(): void
	{
		$conditions = [
			'date_from' => '2023-11-01',
			'date_until' => '2023-12-31',
			'time_from' => '04:00:00',
			'time_until' => '12:00:00',
			'weektimes' => []
		];

		$this->conditionalMock = new Conditional($conditions);
		$result = $this->conditionalMock->determineExprAttribute();

		$expected = 'expr="adapi-compare(substring-before(adapi-date(), \'T\'), \'2023-11-01\')&gt;=0 and adapi-compare(substring-before(adapi-date(), \'T\'), \'2023-12-31\')&lt;=0 and adapi-compare(substring-after(adapi-date(), \'T\'), \'04:00:00\')&gt;=0 and adapi-compare(substring-after(adapi-date(), \'T\'), \'12:00:00\')&lt;=0" ';
		$this->assertSame($expected, $result);
	}


	#[Group('units')]
	public function testDetermineExprAttributeWithTimeConditions(): void
	{
		$conditions = [
			'date_from' => '0000-00-00',
			'date_until' => '0000-00-00',
			'time_from' => '08:00:00',
			'time_until' => '18:00:00',
			'weektimes' => []
		];

		$this->conditionalMock = new Conditional($conditions);
		$result = $this->conditionalMock->determineExprAttribute();

		$expected = 'expr="adapi-compare(substring-after(adapi-date(), \'T\'), \'08:00:00\')&gt;=0 and adapi-compare(substring-after(adapi-date(), \'T\'), \'18:00:00\')&lt;=0" ';
		$this->assertSame($expected, $result);
	}

	#[Group('units')]
	public function testDetermineExprAttributeWithWeektimesConditions(): void
	{
		$conditions = [
			'date_from' => '0000-00-00',
			'date_until' => '0000-00-00',
			'time_from' => '00:00:00',
			'time_until' => '00:00:00',
			'weektimes' => [
				1 => ['from' => 32, 'until' => 48], // 08:00:00 to 12:00:00
				2 => ['from' => 64, 'until' => 80]  // 16:00:00 to 20:00:00
			]
		];

		$this->conditionalMock = new Conditional($conditions);
		$result = $this->conditionalMock->determineExprAttribute();

		$expectedWeektimes = '((0=adapi-weekday() and adapi-compare(substring-after(adapi-date(), \'T\'), \'08:00:00\')&gt;=0 and adapi-compare(substring-after(adapi-date(), \'T\'), \'12:00:00\')&lt;=0) or (1=adapi-weekday() and adapi-compare(substring-after(adapi-date(), \'T\'), \'16:00:00\')&gt;=0 and adapi-compare(substring-after(adapi-date(), \'T\'), \'20:00:00\')&lt;=0))';
		$expected = 'expr="' . $expectedWeektimes . '" ';

		$this->assertSame($expected, $result);
	}

	#[Group('units')]
	public function testDetermineExprAttributeWithWeektimesDateimeCombination(): void
	{
		$conditions = [
			'date_from' => '2024-02-01',
			'date_until' => '2024-05-31',
			'time_from' => '00:00:00',
			'time_until' => '00:00:00',
			'weektimes' => [
				1 => ['from' => 0, 'until' => 96]
			]
		];

		$this->conditionalMock = new Conditional($conditions);
		$result = $this->conditionalMock->determineExprAttribute();

		$expectedWeektimes = 'adapi-compare(substring-before(adapi-date(), \'T\'), \'2024-02-01\')&gt;=0 and adapi-compare(substring-before(adapi-date(), \'T\'), \'2024-05-31\')&lt;=0 and ((0=adapi-weekday() and adapi-compare(substring-after(adapi-date(), \'T\'), \'00:00:00\')&gt;=0 and adapi-compare(substring-after(adapi-date(), \'T\'), \'23:59:59\')&lt;=0))';
		$expected = 'expr="' . $expectedWeektimes . '" ';

		$this->assertSame($expected, $result);
	}
}
