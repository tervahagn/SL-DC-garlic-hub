<?php

namespace App\Modules\Playlists\Helper\Compose;

use App\Framework\Core\Config\Config;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Modules\Playlists\Helper\PlaylistMode;
use App\Modules\Playlists\Services\AclValidator;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class RightsChecker
{
	CONST string MODULE_NAME = 'playlists';

	private readonly Translator $translator;
	private readonly AclValidator $aclValidator;

	/**
	 * @param Translator $translator
	 * @param AclValidator $aclValidator
	 */
	public function __construct(Translator $translator, AclValidator $aclValidator)
	{
		$this->translator = $translator;
		$this->aclValidator = $aclValidator;
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function checkInsertPlaylist(int $timeLimit): array
	{
		if ($timeLimit > 0)
			return [];

		return [
			'LANG_INSERT_PLAYLISTS' => $this->translator->translate('insert_playlists', self::MODULE_NAME),
			'LANG_INSERT_EXTERNAL_PLAYLISTS' => $this->translator->translate('insert_external_playlists', self::MODULE_NAME)
		];
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function checkInsertTemplates(): array
	{
		if ($this->aclValidator->getConfig()->getEdition() === Config::PLATFORM_EDITION_EDGE)
			return [];

		return [
			'LANG_INSERT_TEMPLATES' => $this->translator->translate('insert_templates', self::MODULE_NAME),
		];

	}

	public function checkInsertChannels():array
	{
		if ($this->aclValidator->getConfig()->getEdition() === Config::PLATFORM_EDITION_EDGE)
			return [];

		return [
			'LANG_INSERT_CHANNELS' => $this->translator->translate('insert_channels', self::MODULE_NAME),
		];

	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function checkTimeLimit(int $timeLimit)
	{
		if ($timeLimit === 0)
			return [];

		return [
			'LANG_PLAYLIST_REMAIN_DURATION' => $this->translator->translate('remain_duration', self::MODULE_NAME),
		];
	}

}