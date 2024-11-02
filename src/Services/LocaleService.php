<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LocaleService
{
	private const LANGUAGES = [
		'de_DE' => 'Deutsch',
		'en_US' => 'English',
		'fr_FR' => 'Français',
		'es_ES' => 'Español',
		'ru_RU' => 'Русский'
	];

	private ?SessionInterface $session;

	public function __construct(RequestStack $requestStack)
	{
		$this->session = $requestStack->getSession();
	}

	public function getAvailableLanguages(): array
	{
		return self::LANGUAGES;
	}

	public function getCurrentLocale(): string
	{
		return $this->session ? $this->session->get('_locale', 'en_US') : 'en_US';
	}

	public function setLocale(string $locale): void
	{
		if ($this->session && array_key_exists($locale, self::LANGUAGES)) {
			$this->session->set('_locale', $locale);
		}
	}
}
