<?php

namespace Tests\Unit\Modules\Playlists\Helper\Settings;

use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Helper\Settings\Parameters;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ParametersTest extends TestCase
{
	private readonly Sanitizer&MockObject $sanitizerMock;
	private readonly Session&MockObject $sessionMock;
	private readonly Parameters $parameters;

	/**
	 * @throws ModuleException
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->sanitizerMock = $this->createMock(Sanitizer::class);
		$this->sessionMock   = $this->createMock(Session::class);

		$this->parameters    = new Parameters($this->sanitizerMock, $this->sessionMock);
	}

	#[Group('units')]
	public function testConstructor()
	{
		$this->assertCount(2, $this->parameters->getCurrentParameters());
		$this->assertSame('playlists', $this->parameters->getModuleName());
		$this->assertInstanceOf(Parameters::class, $this->parameters);
	}

	#[Group('units')]
	public function testAddPlaylistMode()
	{
		$this->assertFalse($this->parameters->hasParameter(Parameters::PARAMETER_PLAYLIST_MODE));
		$this->parameters->addPlaylistMode();
		$this->assertCount(3, $this->parameters->getCurrentParameters());
		$this->assertTrue($this->parameters->hasParameter(Parameters::PARAMETER_PLAYLIST_MODE));
	}

	#[Group('units')]
	public function testAddPlaylistId()
	{
		$this->assertFalse($this->parameters->hasParameter(Parameters::PARAMETER_PLAYLIST_ID));
		$this->parameters->addPlaylistId();
		$this->assertCount(3, $this->parameters->getCurrentParameters());
		$this->assertTrue($this->parameters->hasParameter(Parameters::PARAMETER_PLAYLIST_ID));
	}

	#[Group('units')]
	public function testAddTimeLimit()
	{
		$this->assertFalse($this->parameters->hasParameter(Parameters::PARAMETER_TIME_LIMIT));
		$this->parameters->addTimeLimit();
		$this->assertCount(3, $this->parameters->getCurrentParameters());
		$this->asserttrue($this->parameters->hasParameter(Parameters::PARAMETER_TIME_LIMIT));
	}

}
