<?php

namespace Tests\Unit\Modules\Player\Services;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\Enums\PlayerStatus;
use App\Modules\Player\IndexCreation\IndexProvider;
use App\Modules\Player\IndexCreation\PlayerDataAssembler;
use App\Modules\Player\Services\PlayerIndexService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PlayerIndexServiceTest extends TestCase
{
	private readonly PlayerDataAssembler $playerDataAssemblerMock;
	private readonly IndexProvider $indexProviderMock;
	private readonly LoggerInterface $loggerMock;
	private PlayerEntity $playerEntityMock;
	private PlayerIndexService $service;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->playerDataAssemblerMock = $this->createMock(PlayerDataAssembler::class);
		$this->indexProviderMock       = $this->createMock(IndexProvider::class);
		$this->loggerMock              = $this->createMock(LoggerInterface::class);

		$this->playerEntityMock = $this->createMock(PlayerEntity::class);
		$this->service = new PlayerIndexService(
			$this->playerDataAssemblerMock,
			$this->indexProviderMock,
			$this->loggerMock
		);
	}

	#[Group('units')]
	public function testHandleIndexRequestLocalPlayer(): void
	{
		$userAgent   = 'ValidUserAgent';
		$localPlayer = true;
		$filePath    = '/path/to/index';

		$this->loggerMock->expects($this->once())->method('info')
			->with('Connection from: ' . $userAgent);

		$this->playerDataAssemblerMock->expects($this->once())->method('parseUserAgent')
			->with($userAgent)
			->willReturn(true);

		$this->playerDataAssemblerMock->expects($this->once())->method('handleLocalPlayer')
			->willReturn($this->playerEntityMock);

		$this->playerEntityMock->method('getStatus')->willReturn(PlayerStatus::RELEASED->value);
		$this->indexProviderMock->expects($this->once())->method('handleReleased')
			->with($this->playerEntityMock);

		$this->indexProviderMock->expects($this->once())->method('getFilePath')
			->willReturn($filePath);

		$this->service->setUID(1);
		$result = $this->service->handleIndexRequest($userAgent, $localPlayer);

		$this->assertSame($filePath, $result);
	}

	#[Group('units')]
	public function testHandleIndexRequestUnreleasedRemotePlayer(): void
	{
		$userAgent   = 'ValidUserAgent';
		$localPlayer = false;
		$filePath    = '/path/to/index';

		$this->loggerMock->expects($this->once())->method('info')
			->with('Connection from: ' . $userAgent);

		$this->playerDataAssemblerMock->expects($this->once())->method('parseUserAgent')
			->with($userAgent)
			->willReturn(true);

		$this->playerDataAssemblerMock->expects($this->once())->method('fetchDatabase')
			->willReturn($this->playerEntityMock);

		$this->playerEntityMock->method('getStatus')->willReturn(PlayerStatus::UNRELEASED->value);
		$this->indexProviderMock->expects($this->once())->method('handleUnreleased');

		$this->indexProviderMock->expects($this->once())->method('getFilePath')
			->willReturn($filePath);

		$this->service->setUID(1);
		$result = $this->service->handleIndexRequest($userAgent, $localPlayer);

		$this->assertSame($filePath, $result);
	}

	#[Group('units')]
	public function testHandleIndexRequestNewRemotePlayer(): void
	{
		$userAgent   = 'ValidUserAgent';
		$localPlayer = false;
		$filePath    = '/path/to/index';

		$this->loggerMock->expects($this->once())->method('info')
			->with('Connection from: ' . $userAgent);

		$this->playerDataAssemblerMock->expects($this->once())->method('parseUserAgent')
			->with($userAgent)
			->willReturn(true);

		$this->playerEntityMock->method('getStatus')->willReturn(PlayerStatus::UNREGISTERED->value);
		$this->playerDataAssemblerMock->expects($this->once())->method('insertNewPlayer')
			->with(1)
			->willReturn($this->playerEntityMock);

		$this->indexProviderMock->expects($this->once())->method('handleNew')
			->with($this->playerEntityMock);

		$this->indexProviderMock->expects($this->once())->method('getFilePath')
			->willReturn($filePath);

		$this->service->setUID(1);
		$result = $this->service->handleIndexRequest($userAgent, $localPlayer);

		$this->assertSame($filePath, $result);
	}

	#[Group('units')]
	public function testHandleIndexRequestDebugFtp(): void
	{
		$userAgent   = 'ValidUserAgent';
		$localPlayer = false;
		$filePath    = '/path/to/index';

		$this->loggerMock->expects($this->once())->method('info')
			->with('Connection from: ' . $userAgent);

		$this->playerDataAssemblerMock->expects($this->once())->method('parseUserAgent')
			->with($userAgent)
			->willReturn(true);

		$this->playerDataAssemblerMock->expects($this->once())->method('fetchDatabase')
			->willReturn($this->playerEntityMock);

		$this->playerEntityMock->method('getStatus')->willReturn(PlayerStatus::DEBUG_FTP->value);
		$this->indexProviderMock->expects($this->once())->method('handleTestSMil');

		$this->indexProviderMock->expects($this->once())->method('getFilePath')
			->willReturn($filePath);

		$this->service->setUID(1);
		$result = $this->service->handleIndexRequest($userAgent, $localPlayer);

		$this->assertSame($filePath, $result);
	}

	#[Group('units')]
	public function testHandleIndexRequestCorrectSmil(): void
	{
		$userAgent   = 'ValidUserAgent';
		$localPlayer = false;
		$filePath    = '/path/to/index';

		$this->loggerMock->expects($this->once())->method('info')
			->with('Connection from: ' . $userAgent);

		$this->playerDataAssemblerMock->expects($this->once())->method('parseUserAgent')
			->with($userAgent)
			->willReturn(true);

		$this->playerDataAssemblerMock->expects($this->once())->method('fetchDatabase')
			->willReturn($this->playerEntityMock);

		$this->playerEntityMock->method('getStatus')->willReturn(PlayerStatus::TEST_SMIL_OK->value);
		$this->indexProviderMock->expects($this->once())->method('handleCorrectSMil');

		$this->indexProviderMock->expects($this->once())->method('getFilePath')
			->willReturn($filePath);

		$this->service->setUID(1);
		$result = $this->service->handleIndexRequest($userAgent, $localPlayer);

		$this->assertSame($filePath, $result);
	}

	#[Group('units')]
	public function testHandleIndexRequestCorruptSmil(): void
	{
		$userAgent   = 'ValidUserAgent';
		$localPlayer = false;
		$filePath    = '/path/to/index';

		$this->loggerMock->expects($this->once())->method('info')
			->with('Connection from: ' . $userAgent);

		$this->playerDataAssemblerMock->expects($this->once())->method('parseUserAgent')
			->with($userAgent)
			->willReturn(true);

		$this->playerDataAssemblerMock->expects($this->once())->method('fetchDatabase')
			->willReturn($this->playerEntityMock);

		$this->playerEntityMock->method('getStatus')->willReturn(PlayerStatus::TEST_SMIL_ERROR->value);
		$this->indexProviderMock->expects($this->once())->method('handleCorruptSMIL');

		$this->indexProviderMock->expects($this->once())->method('getFilePath')
			->willReturn($filePath);

		$this->service->setUID(1);
		$result = $this->service->handleIndexRequest($userAgent, $localPlayer);

		$this->assertSame($filePath, $result);
	}

	#[Group('units')]
	public function testHandleIndexRequestCorruptContent(): void
	{
		$userAgent   = 'ValidUserAgent';
		$localPlayer = false;
		$filePath    = '/path/to/index';

		$this->loggerMock->expects($this->once())->method('info')
			->with('Connection from: ' . $userAgent);

		$this->playerDataAssemblerMock->expects($this->once())->method('parseUserAgent')
			->with($userAgent)
			->willReturn(true);

		$this->playerDataAssemblerMock->expects($this->once())->method('fetchDatabase')
			->willReturn($this->playerEntityMock);

		$this->playerEntityMock->method('getStatus')->willReturn(PlayerStatus::TEST_NO_CONTENT->value);
		$this->indexProviderMock->expects($this->once())->method('handleCorruptContent');

		$this->indexProviderMock->expects($this->once())->method('getFilePath')
			->willReturn($filePath);

		$this->service->setUID(1);
		$result = $this->service->handleIndexRequest($userAgent, $localPlayer);

		$this->assertSame($filePath, $result);
	}

	#[Group('units')]
	public function testHandleIndexRequestCorruptPrefetch(): void
	{
		$userAgent   = 'ValidUserAgent';
		$localPlayer = false;
		$filePath    = '/path/to/index';

		$this->loggerMock->expects($this->once())->method('info')
			->with('Connection from: ' . $userAgent);

		$this->playerDataAssemblerMock->expects($this->once())->method('parseUserAgent')
			->with($userAgent)
			->willReturn(true);

		$this->playerDataAssemblerMock->expects($this->once())->method('fetchDatabase')
			->willReturn($this->playerEntityMock);

		$this->playerEntityMock->method('getStatus')->willReturn(PlayerStatus::TEST_NO_PREFETCH->value);
		$this->indexProviderMock->expects($this->once())->method('handleCorruptPrefetchContent');

		$this->indexProviderMock->expects($this->once())->method('getFilePath')
			->willReturn($filePath);

		$this->service->setUID(1);
		$result = $this->service->handleIndexRequest($userAgent, $localPlayer);

		$this->assertSame($filePath, $result);
	}

	#[Group('units')]
	public function testHandleIndexRequestWithInvalidAgentHandlesForbidden(): void
	{
		$userAgent = 'InvalidUserAgent';
		$localPlayer = false;

		$this->playerDataAssemblerMock->expects($this->once())->method('parseUserAgent')
			->with($userAgent)
			->willReturn(false);

		$this->indexProviderMock->expects($this->once())->method('handleForbidden');

		$this->loggerMock->expects($this->once())->method('info')
			->with('Connection from: ' . $userAgent);

		$this->service->handleIndexRequest($userAgent, $localPlayer);
	}

	#[Group('units')]
	public function testHandleIndexRequestWhenExceptionThrownLogsErrorAndReturnsEmptyString(): void
	{
		$userAgent = 'ExceptionUserAgent';
		$localPlayer = true;

		$this->playerDataAssemblerMock->expects($this->once())->method('parseUserAgent')
			->with($userAgent)
			->willThrowException(new \RuntimeException('Parsing error'));

		$this->loggerMock->expects($this->once())->method('info')
			->with('Connection from: ' . $userAgent);

		$this->loggerMock->expects($this->once())->method('error')
			->with('Error fetch Index: Parsing error');

		$result = $this->service->handleIndexRequest($userAgent, $localPlayer);

		$this->assertSame('', $result);
	}


}
