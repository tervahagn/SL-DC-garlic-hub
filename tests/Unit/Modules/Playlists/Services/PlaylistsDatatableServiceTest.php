<?php

namespace Tests\Unit\Modules\Playlists\Services;

use App\Framework\Exceptions\CoreException;
use App\Modules\Playlists\Helper\Datatable\Parameters;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use App\Modules\Playlists\Services\AclValidator;
use App\Modules\Playlists\Services\PlaylistsDatatableService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PlaylistsDatatableServiceTest extends TestCase
{
	protected readonly LoggerInterface $loggerMock;
	private readonly PlaylistsRepository $repositoryMock;
	private readonly Parameters $parametersMock;
	private readonly AclValidator $aclValidatorMock;
	private readonly PlaylistsDatatableService $service;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->loggerMock       = $this->createMock(LoggerInterface::class);
		$this->repositoryMock   = $this->createMock(PlaylistsRepository::class);
		$this->parametersMock   = $this->createMock(Parameters::class);
		$this->aclValidatorMock = $this->createMock(AclValidator::class);

		$this->service = new PlaylistsDatatableService($this->repositoryMock, $this->parametersMock, $this->aclValidatorMock, $this->loggerMock);
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

		$this->repositoryMock->expects($this->once())->method('findAllFiltered')
			->with(['empty'])
			->willReturn(['result']);

		$this->service->loadDatatable();

		$this->assertSame(12, $this->service->getCurrentTotalResult());
		$this->assertSame(['result'], $this->service->getCurrentFilterResults());
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


		$this->repositoryMock->expects($this->once())->method('findAllFilteredByUID')
			->with(['empty'], 789)
			->willReturn(['result']);

		$this->service-> loadDatatable();

		$this->assertSame(12, $this->service->getCurrentTotalResult());
		$this->assertSame(['result'], $this->service->getCurrentFilterResults());
	}
}
