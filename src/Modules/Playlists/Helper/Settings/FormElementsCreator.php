<?php

namespace App\Modules\Playlists\Helper\Settings;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Utils\FormParameters\BaseEditParameters;
use App\Framework\Utils\Html\FieldInterface;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

readonly class FormElementsCreator
{
	private FormBuilder $formBuilder;

	private Translator $translator;


	public function __construct(FormBuilder $formBuilder, Translator $translator)
	{
		$this->formBuilder = $formBuilder;
		$this->translator = $translator;
	}

	public function prepareForm(array $form): array
	{
		return $this->formBuilder->prepareForm($form);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function createPlaylistNameField(string $value): FieldInterface
	{
		return  $this->formBuilder->createField([
			'type' => FieldType::TEXT,
			'id' => 'playlist_name',
			'name' => 'playlist_name',
			'title' => $this->translator->translate('playlist_name', 'playlists'),
			'label' => $this->translator->translate('playlist_name', 'playlists'),
			'value' => $value,
			'rules' => ['required' => true, 'minlength' => 2],
			'default_value' => ''
		]);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function createUIDField(string $value, string $username, int $UID): FieldInterface
	{
		return $this->formBuilder->createField([
			'type'          => FieldType::AUTOCOMPLETE,
			'id'            => 'UID',
			'name'          => 'UID',
			'title'         => $this->translator->translate('owner', 'main'),
			'label'         => $this->translator->translate('owner', 'main'),
			'value'         => $value,
			'data-label'    => $username,
			'default_value' => $UID
		]);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function createTimeLimitField(int $value, int $defaultValue): FieldInterface
	{
		return $this->formBuilder->createField([
			'type' => FieldType::NUMBER,
			'id' => 'time_limit',
			'name' => 'time_limit',
			'title' => $this->translator->translate('time_limit_explanation', 'playlists'),
			'label' => $this->translator->translate('time_limit', 'playlists'),
			'value' => $value,
			'min'   => 0,
			'default_value' => $defaultValue
		]);
	}

	/**
	 * @throws FrameworkException
	 */
	public function createHiddenPlaylistIdField(int $value): FieldInterface
	{
		return $this->formBuilder->createField([
			'type' => FieldType::HIDDEN,
			'id' => 'playlist_id',
			'name' => 'playlist_id',
			'value' => $value,
		]);
	}

	/**
	 * @throws FrameworkException
	 */
	public function createPlaylistModeField(string $value): FieldInterface
	{
		return $this->formBuilder->createField([
			'type' => FieldType::HIDDEN,
			'id' => 'playlist_mode',
			'name' => 'playlist_mode',
			'value' => $value,
		]);
	}

	/**
	 * @throws FrameworkException
	 */
	public function createCSRFTokenField(): FieldInterface
	{
		return $this->formBuilder->createField([
			'type' => FieldType::CSRF,
			'id'   => BaseEditParameters::PARAMETER_CSRF_TOKEN,
			'name' => BaseEditParameters::PARAMETER_CSRF_TOKEN,
		]);
	}

}