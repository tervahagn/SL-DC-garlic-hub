<?php

namespace Tests\Unit\Modules\Player\Helper\Datatable;

use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Player\Helper\Datatable\Parameters;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ParametersTest extends TestCase
{
	private Session&MockObject $sessionMock;
	private Parameters $parameters;

	/**
	 * @throws ModuleException
	 * @throws Exception
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$sanitizerMock = $this->createMock(Sanitizer::class);
		$this->sessionMock   = $this->createMock(Session::class);

		$this->parameters    = new Parameters($sanitizerMock, $this->sessionMock);
	}

	#[Group('units')]
	public function testConstructor()
	{
		$this->assertCount(8, $this->parameters->getCurrentParameters());
		$this->assertSame('player', $this->parameters->getModuleName());
		$this->assertInstanceOf(Parameters::class, $this->parameters);
	}
}
