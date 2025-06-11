<?php

namespace Tests\Unit\Modules\Player\Dashboard;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Modules\Player\Dashboard\PlayerDashboard;
use App\Modules\Player\Services\PlayerService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;

class PlayerDashboardTest extends TestCase
{
	private PlayerDashboard $playerDashboard;
	private PlayerService&MockObject $playerServiceMock;
	private Translator&MockObject $translatorMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->playerServiceMock = $this->createMock(PlayerService::class);
		$this->translatorMock = $this->createMock(Translator::class);

		$this->playerDashboard = new PlayerDashboard(
			$this->playerServiceMock,
			$this->translatorMock
		);
	}

	#[Group('units')]
	public function testGetId(): void
	{
		$result = $this->playerDashboard->getId();
		$this->assertSame('player', $result);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testGetTitle(): void
	{
		$this->translatorMock
			->expects($this->once())
			->method('translate')
			->with('dashboard', 'player')
			->willReturn('Player Dashboard');

		$result = $this->playerDashboard->getTitle();

		$this->assertSame('Player Dashboard', $result);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testRenderContent(): void
	{
		$data = [
			'active' => 10,
			'pending' => 5,
			'inactive' => 3,
		];

		$this->playerServiceMock
			->expects($this->once())
			->method('findAllForDashboard')
			->willReturn($data);

		$this->translatorMock
			->expects($this->exactly(3))
			->method('translate')
			->willReturnMap([
				['count_active', 'player', [], 'Active Players'],
				['count_pending', 'player', [], 'Pending Players'],
				['count_inactive', 'player', [], 'Inactive Players'],
			]);

		$result = $this->playerDashboard->renderContent();

		$expected = '<ul>
	<li><strong>Active Players:</strong><span>10</span></li>
	<li><strong>Pending Players:</strong><span>5</span></li>
	<li><strong>Inactive Players:</strong><span>3</span></li>
</ul>';

		$this->assertSame($expected, $result);
	}
}
