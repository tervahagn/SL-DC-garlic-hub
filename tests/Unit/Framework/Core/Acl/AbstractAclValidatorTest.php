<?php

namespace Tests\Unit\Framework\Core\Acl;

use App\Framework\Core\Acl\AbstractAclValidator;
use App\Framework\Core\Acl\AclHelper;
use App\Framework\Core\Config\Config;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ConcreteAclValidator extends AbstractAclValidator
{

}

class AbstractAclValidatorTest extends TestCase
{
	private readonly ConcreteAclValidator $aclValidator;
	private readonly AclHelper $aclHelperMock;

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
	public function testGetConfig()
	{
		$configMock = $this->createMock(Config::class);
		$this->aclHelperMock->expects($this->once())->method('getConfig')->willReturn($configMock);
		$this->assertSame($configMock, $this->aclValidator->getConfig());
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testIsSimpleAdminReturnsTrueWhenIsModuleAdmin()
	{
		$this->aclHelperMock->expects($this->once())->method('isModuleAdmin')->with(1, 'testModule')->willReturn(true);
		$this->aclHelperMock->expects($this->never())->method('isSubAdmin');

		$this->assertTrue($this->aclValidator->isSimpleAdmin(1));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testIsSimpleAdminReturnsTrueWhenIsSubAdmin()
	{
		$this->aclHelperMock->expects($this->once())->method('isModuleAdmin')->with(1, 'testModule')->willReturn(false);
		$this->aclHelperMock->expects($this->once())->method('isSubAdmin')->with(1, 'testModule')->willReturn(true);

		$this->assertTrue($this->aclValidator->isSimpleAdmin(1));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testIsSimpleAdminReturnsFalse()
	{
		$this->aclHelperMock->expects($this->once())->method('isModuleAdmin')->with(1, 'testModule')->willReturn(false);
		$this->aclHelperMock->expects($this->once())->method('isSubAdmin')->with(1, 'testModule')->willReturn(false);

		$this->assertFalse($this->aclValidator->isSimpleAdmin(1));
	}


}
