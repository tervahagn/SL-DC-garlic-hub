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
	public function testConstructor(): void
	{
		$this->assertCount(7, $this->parameters->getCurrentParameters());
		$this->assertSame('player', $this->parameters->getModuleName());
	}

	#[Group('units')]
	public function testAddActivity(): void
	{
		// Call the method to add the activity parameter
		$this->parameters->addActivity();

		// Assert that the parameter is now present
		$this->assertTrue($this->parameters->hasParameter(Parameters::PARAMETER_ACTIVITY));

		// Assert that the parameter has the correct type and default value
		$currentParameters = $this->parameters->getCurrentParameters();
		$this->assertArrayHasKey(Parameters::PARAMETER_ACTIVITY, $currentParameters);
		$this->assertSame('', $currentParameters[Parameters::PARAMETER_ACTIVITY]['default_value']);
		$this->assertSame(\App\Framework\Utils\FormParameters\ScalarType::STRING, $currentParameters[Parameters::PARAMETER_ACTIVITY]['scalar_type']);
	}


}
