<?php

namespace Tests\Unit\Modules\Playlists\Helper\Settings;

use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\FormParameters\BaseEditParameters;
use App\Framework\Utils\Html\FieldInterface;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Playlists\Helper\Settings\FormElementsCreator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class CollectorTest extends TestCase
{
	private readonly FormBuilder $formBuilderMock;
	private readonly Translator $translatorMock;
	private readonly FormElementsCreator $collector;

	protected function setUp(): void
	{
		$this->formBuilderMock = $this->createMock(FormBuilder::class);
		$this->translatorMock = $this->createMock(Translator::class);

		$this->collector = new FormElementsCreator($this->formBuilderMock, $this->translatorMock);
	}

	#[Group('units')]
	public function testPrepareForm(): void
	{
		$formData = ['field1' => 'value1', 'field2' => 'value2'];
		$preparedForm = ['preparedField1', 'preparedField2'];

		$this->formBuilderMock
			->expects($this->once())
			->method('prepareForm')
			->with($formData)
			->willReturn($preparedForm);

		$result = $this->collector->prepareForm($formData);

		$this->assertSame($preparedForm, $result);
	}


	#[Group('units')]
	public function testCreatePlaylistNameField(): void
	{
		$value = 'My Playlist';
		$expectedField = $this->createMock(FieldInterface::class);

		$this->translatorMock->expects($this->exactly(2))->method('translate')
			->with('playlist_name', 'playlists')
			->willReturn('Playlist name');

		$this->formBuilderMock
			->expects($this->once())
			->method('createField')
			->with([
				'type' => FieldType::TEXT,
				'id' => 'playlist_name',
				'name' => 'playlist_name',
				'title' => 'Playlist name',
				'label' => 'Playlist name',
				'value' => $value,
				'rules' => ['required' => true, 'minlength' => 2],
				'default_value' => ''
			])
			->willReturn($expectedField);

		$result = $this->collector->createPlaylistNameField($value);

		$this->assertSame($expectedField, $result);
	}

	#[Group('units')]
	public function testCreateUIDField(): void
	{
		$value = '12345';
		$username = 'user1';
		$UID = 99;
		$expectedField = $this->createMock(FieldInterface::class);

		$this->translatorMock->expects($this->exactly(2))
			->method('translate')
			->with('owner', 'main')
			->willReturn('Owner');

		$this->formBuilderMock
			->expects($this->once())
			->method('createField')
			->with([
				'type' => FieldType::AUTOCOMPLETE,
				'id' => 'UID',
				'name' => 'UID',
				'title' => 'Owner',
				'label' => 'Owner',
				'value' => $value,
				'data-label' => $username,
				'default_value' => $UID,
			])
			->willReturn($expectedField);

		$result = $this->collector->createUIDField($value, $username, $UID);

		$this->assertSame($expectedField, $result);
	}

	#[Group('units')]
	public function testCreateTimeLimitField(): void
	{
		$value = 120;
		$defaultValue = 60;
		$expectedField = $this->createMock(FieldInterface::class);

		$this->translatorMock->expects($this->exactly(2))->method('translate')
			->willReturnMap([
				['time_limit_explanation', 'playlists', [], 'Explanation for time limit'],
				['time_limit', 'playlists', [], 'Time Limit']
			]);

		$this->formBuilderMock
			->expects($this->once())
			->method('createField')
			->with([
				'type' => FieldType::NUMBER,
				'id' => 'time_limit',
				'name' => 'time_limit',
				'title' => 'Explanation for time limit',
				'label' => 'Time Limit',
				'value' => $value,
				'min' => 0,
				'default_value' => $defaultValue
			])
			->willReturn($expectedField);

		$result = $this->collector->createTimeLimitField($value, $defaultValue);
	}

	#[Group('units')]
	public function testCreateHiddenPlaylistIdField(): void
	{
		$value = 101;
		$expectedField = $this->createMock(FieldInterface::class);

		$this->formBuilderMock
			->expects($this->once())
			->method('createField')
			->with([
				'type' => FieldType::HIDDEN,
				'id' => 'playlist_id',
				'name' => 'playlist_id',
				'value' => $value,
			])
			->willReturn($expectedField);

		$result = $this->collector->createHiddenPlaylistIdField($value);

		$this->assertSame($expectedField, $result);
	}

	#[Group('units')]
	public function testCreatePlaylistModeField(): void
	{
		$value = 2;
		$expectedField = $this->createMock(FieldInterface::class);

		$this->formBuilderMock
			->expects($this->once())
			->method('createField')
			->with([
				'type' => FieldType::HIDDEN,
				'id' => 'playlist_mode',
				'name' => 'playlist_mode',
				'value' => $value,
			])
			->willReturn($expectedField);

		$result = $this->collector->createPlaylistModeField($value);
		$this->assertSame($expectedField, $result);
	}

	#[Group('units')]
	public function testCreateCSRFTokenField(): void
	{
		$expectedField = $this->createMock(FieldInterface::class);

		$this->formBuilderMock
			->expects($this->once())
			->method('createField')
			->with([
				'type' => FieldType::CSRF,
				'id' => BaseEditParameters::PARAMETER_CSRF_TOKEN,
				'name' => BaseEditParameters::PARAMETER_CSRF_TOKEN,
			])
			->willReturn($expectedField);

		$result = $this->collector->createCSRFTokenField();

		$this->assertSame($expectedField, $result);
	}
}
