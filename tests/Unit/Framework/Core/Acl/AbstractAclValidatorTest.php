<?php

namespace Tests\Unit\Framework\Core\Acl;

use App\Framework\Core\Acl\AbstractAclValidator;
use App\Framework\Core\Acl\AclHelper;
use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConcreteAclValidator extends AbstractAclValidator
{

}

class AbstractAclValidatorTest extends TestCase
{
	private ConcreteAclValidator $aclValidator;
	private AclHelper&MockObject $aclHelperMock;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$this->aclHelperMock = $this->createMock(AclHelper::class);
		$this->aclValidator = new ConcreteAclValidator('testModule', $this->aclHelperMock);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testGetConfig(): void
	{
		$configMock = $this->createMock(Config::class);
		$this->aclHelperMock->expects($this->once())->method('getConfig')->willReturn($configMock);
		$this->assertSame($configMock, $this->aclValidator->getConfig());
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsSimpleAdminReturnsTrueWhenIsModuleAdmin(): void
	{
		$this->aclHelperMock->expects($this->once())->method('isModuleAdmin')->with(1, 'testModule')->willReturn(true);
		$this->aclHelperMock->expects($this->never())->method('isSubAdmin');

		$this->assertTrue($this->aclValidator->isSimpleAdmin(1));
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsSimpleAdminReturnsTrueWhenIsSubAdmin(): void
	{
		$this->aclHelperMock->expects($this->once())->method('isModuleAdmin')->with(1, 'testModule')->willReturn(false);
		$this->aclHelperMock->expects($this->once())->method('isSubAdmin')->with(1, 'testModule')->willReturn(true);

		$this->assertTrue($this->aclValidator->isSimpleAdmin(1));
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsSimpleAdminReturnsFalse(): void
	{
		$this->aclHelperMock->expects($this->once())->method('isModuleAdmin')->with(1, 'testModule')->willReturn(false);
		$this->aclHelperMock->expects($this->once())->method('isSubAdmin')->with(1, 'testModule')->willReturn(false);

		$this->assertFalse($this->aclValidator->isSimpleAdmin(1));
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsAdminModuleAdmin(): void
	{
		$UID = 1;

		$this->aclHelperMock->expects($this->once())
			->method('isModuleAdmin')
			->with($UID)
			->willReturn(true);

		$result = $this->aclValidator->isAdmin($UID, []);

		$this->assertTrue($result);
	}

	/**
	 * @throws CoreException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsAdminSubAdminAccess(): void
	{
		$UID = 1;
		$unit = ['UID' => 1, 'company_id' => 4];

		$configMock = $this->createMock(Config::class);
		$this->aclHelperMock->expects($this->once())->method('getConfig')->willReturn($configMock);
		$configMock->expects($this->once())->method('getEdition')->willReturn(Config::PLATFORM_EDITION_ENTERPRISE);

		$this->aclHelperMock->method('isModuleAdmin')
			->with($UID)
			->willReturn(false);

		$this->aclHelperMock->method('isSubAdmin')
			->with($UID)
			->willReturn(true);

		$this->aclHelperMock->method('hasSubAdminAccessOnCompany')
			->with($UID, 4)
			->willReturn(true);


		$result = $this->aclValidator->isAdmin($UID, $unit);

		$this->assertTrue($result);
	}


	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testIsAdminFails(): void
	{
		$UID = 1;
		$unit = ['UID' => 1, 'company_id' => 4];
		$result = $this->aclValidator->isAdmin($UID, $unit);

		$this->assertFalse($result);
	}

}
