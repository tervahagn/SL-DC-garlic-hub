<?php

namespace Tests\Unit\Modules\Users\Services;

use App\Framework\Exceptions\CoreException;
use App\Modules\Users\Helper\Datatable\Parameters;
use App\Modules\Users\Repositories\Edge\UserMainRepository;
use App\Modules\Users\Services\AclValidator;
use App\Modules\Users\Services\UsersDatatableService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UsersDatatableServiceTest extends TestCase
{
	private UserMainRepository&MockObject $repositoryMock;
	private Parameters&MockObject $parametersMock;
	private AclValidator&MockObject $aclValidatorMock;
	private UsersDatatableService $service;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$loggerMock = $this->createMock(LoggerInterface::class);
		$this->repositoryMock   = $this->createMock(UserMainRepository::class);
		$this->parametersMock   = $this->createMock(Parameters::class);
		$this->aclValidatorMock = $this->createMock(AclValidator::class);

		$this->service = new UsersDatatableService($this->repositoryMock, $this->parametersMock, $this->aclValidatorMock, $loggerMock);
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
			->willReturn([['result' => 1]]);

		$this->service->loadDatatable();

		$this->assertSame(12, $this->service->getCurrentTotalResult());
		$this->assertSame([['result' => 1]], $this->service->getCurrentFilterResults());
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
			->willReturn([['result' => 1]]);

		$this->service->loadDatatable();

		$this->assertSame(12, $this->service->getCurrentTotalResult());
		$this->assertSame([['result' => 1]], $this->service->getCurrentFilterResults());
	}
}
