<?php

namespace Tests\Unit\Modules\Playlists\Helper\Settings;

use App\Framework\Core\Translate\Translator;
use App\Modules\Playlists\Helper\Settings\Parameters;
use App\Modules\Playlists\Helper\Settings\Validator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
	private Validator $validator;
	private MockObject $translatorMock;
	private MockObject $parametersMock;

	protected function setUp(): void
	{
		$this->translatorMock = $this->createMock(Translator::class);
		$this->parametersMock = $this->createMock(Parameters::class);
		$this->validator = new Validator($this->translatorMock, $this->parametersMock);
	}

	#[Group('units')]
	public function testValidateUserInputWithErrors(): void
	{
		$this->parametersMock->method('getValueOfParameter')
			->with(Parameters::PARAMETER_PLAYLIST_NAME)
			->willReturn(null);

		$this->translatorMock->method('translate')
			->willReturnMap([
				['no_playlist_name', 'playlists', [], 'Playlist name is missing.'],
				['parameters_missing', 'playlists', [], 'Parameter are missing.'],
				['playlist_mode_unsupported', 'playlists', [], 'Unsupported Playlist.']
			]);

		$errors = $this->validator->validateUserInput([Parameters::PARAMETER_PLAYLIST_MODE => 'unsupported']);
		$expectedErrors = [
			'Playlist name is missing.',
			'Parameter are missing.',
			 'Unsupported Playlist.'
		];
		$this->assertEquals($expectedErrors, $errors);
	}

	#[Group('units')]
	public function testValidateUserInputPasses(): void
	{
		$this->parametersMock->method('getValueOfParameter')
			->with(Parameters::PARAMETER_PLAYLIST_NAME)
			->willReturn('Playlist name');

		$this->translatorMock->expects($this->never())->method('translate');

		$userInput = [
			'playlist_name' => 'Playlist name',
			Parameters::PARAMETER_PLAYLIST_ID => 12,
			Parameters::PARAMETER_PLAYLIST_MODE => 'multizone'
		];

		$errors = $this->validator->validateUserInput($userInput);
		$this->assertEmpty($errors);
	}



}
