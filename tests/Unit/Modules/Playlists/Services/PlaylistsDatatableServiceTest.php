<?php

namespace Tests\Unit\Modules\Playlists\Services;

use App\Framework\Exceptions\CoreException;
use App\Modules\Playlists\Helper\Datatable\Parameters;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use App\Modules\Playlists\Services\AclValidator;
use App\Modules\Playlists\Services\PlaylistsDatatableService;
use App\Modules\Playlists\Services\PlaylistUsageService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PlaylistsDatatableServiceTest extends TestCase
{
	protected LoggerInterface&MockObject $loggerMock;
	private PlaylistsRepository&MockObject $repositoryMock;
	private Parameters&MockObject $parametersMock;
	private AclValidator&MockObject $aclValidatorMock;
	private PlaylistUsageService&MockObject $playlistUsageServiceMock;
	private PlaylistsDatatableService $service;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->loggerMock       = $this->createMock(LoggerInterface::class);
		$this->repositoryMock   = $this->createMock(PlaylistsRepository::class);
		$this->parametersMock   = $this->createMock(Parameters::class);
		$this->aclValidatorMock = $this->createMock(AclValidator::class);
		$this->playlistUsageServiceMock = $this->createMock(PlaylistUsageService::class);

		$this->service = new PlaylistsDatatableService($this->repositoryMock, $this->parametersMock, $this->aclValidatorMock, $this->playlistUsageServiceMock, $this->loggerMock);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFetchForModuleAdmin(): void
	{
		$this->service->setUID(345);
		$this->aclValidatorMock->expects($this->once())->method('isModuleAdmin')
			->with(345)
			->willReturn(true);

		$this->parametersMock->expects($this->exactly(2))
			->method('getInputParametersArray')
			->willReturn(['empty']);

		$this->repositoryMock->expects($this->once())->method('countAllFiltered')
			->with(['empty'])
			->willReturn(12);

		$result =	[
			0 => ['result1' => 'result1'],
			1 => ['result2' => 1]
		];
		$this->repositoryMock->expects($this->once())->method('findAllFiltered')
			->with(['empty'])
			->willReturn($result);

		$this->service->loadDatatable();

		$this->assertSame(12, $this->service->getCurrentTotalResult());
		$this->assertSame($result, $this->service->getCurrentFilterResults());
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFetchForUser(): void
	{
		$this->service->setUID(789);
		$this->aclValidatorMock->expects($this->once())->method('isModuleAdmin')
			->with(789)
			->willReturn(false);

		$this->parametersMock->expects($this->exactly(2))
			->method('getInputParametersArray')->willReturn(['empty']);

		$this->repositoryMock->expects($this->once())->method('countAllFilteredByUID')
			->with(['empty'], 789)
			->willReturn(12);

		$result =	[
			0 => ['result1' => 'result1'],
			1 => ['result2' => 1]
		];
		$this->repositoryMock->expects($this->once())->method('findAllFilteredByUID')
			->with(['empty'], 789)
			->willReturn($result);

		$this->service->loadDatatable();

		$this->assertSame(12, $this->service->getCurrentTotalResult());
		$this->assertSame($result, $this->service->getCurrentFilterResults());
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testGetPlaylistsInUseWithEmptyIds(): void
	{
		$result = $this->service->getPlaylistsInUse([]);
		$this->assertSame([], $result);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testGetPlaylistsInUseWithValidIds(): void
	{
		$playlistIds = [1, 2, 3];
		$expectedResult = [
			1 => true,
			2 => true,
			3 => true,
		];
		$this->playlistUsageServiceMock->expects($this->once())->method('determinePlaylistsInUse')
			->with($playlistIds)
			->willReturn($expectedResult);
		$result = $this->service->getPlaylistsInUse($playlistIds);

		$this->assertSame($expectedResult, $result);
	}
}
