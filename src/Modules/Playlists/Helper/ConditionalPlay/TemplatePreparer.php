<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);


namespace App\Modules\Playlists\Helper\ConditionalPlay;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\TemplateEngine\AdapterInterface;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class TemplatePreparer
{
	private readonly Translator $translator;
	private readonly AdapterInterface $template;
	/** @var array<string,mixed>  */
	private array $templateData;
	private bool $conditionalPlay = false;

	public function __construct(Translator $translator, AdapterInterface $template)
	{
		$this->translator = $translator;
		$this->template = $template;
	}

	/**
	 * @param array<string,mixed> $conditionalPlayData
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function prepare(int $itemId, array $conditionalPlayData): void
	{
		$dateFrom  = $conditionalPlayData['date_from'] ?? '';
		$dateUtil  = $conditionalPlayData['date_until'] ?? '';
		$timeFrom  = $conditionalPlayData['time_from'] ?? '';
		$timeUntil = $conditionalPlayData['time_until'] ?? '';
		$weekdays  = $conditionalPlayData['weekdays'] ?? 0;
		$weektimes = $conditionalPlayData['weektimes'] ?? 0;

		$this->templateData = [
			'LANG_CONDITIONAL_PLAY' => $this->translator->translate('conditional_play', 'playlists'),
			'LANG_DATE_PERIOD' => $this->translator->translate('validity_period', 'playlists'),
			'LANG_DAILY' => $this->translator->translate('daily', 'main'),
			'LANG_WEEKDAYS' => $this->translator->translate('weekdays', 'playlists'),
			'LANG_FROM'   => $this->translator->translate('from', 'main'),
			'LANG_UNTIL'  => $this->translator->translate('until', 'main'),
			'LANG_SAVE'   => $this->translator->translate('save', 'main'),
			'LANG_CANCEL' => $this->translator->translate('cancel', 'main'),
			'CONDITIONAL_PLAY_ITEM_ID' => $itemId,
			'DATE_FROM'  => $dateFrom,
			'DATE_UNTIL' => $dateUtil,
			'TIME_FROM'  => $timeFrom,
			'TIME_UNTIL' => $timeUntil
		];
		if ($dateFrom !== '' || $dateFrom != '')
		{
			$this->templateData['DATE_PERIOD_CHECKED'] = 'checked';
			$this->conditionalPlay = true;
		}

		if ($timeFrom != '' || $timeUntil != '')
		{
			$this->templateData['TIME_PERIOD_CHECKED'] = 'checked';
			$this->conditionalPlay = true;
		}

		$this->templateData['list_weekdays'] = $this->determineWeekdays($weekdays, $weektimes);

		if ($this->conditionalPlay)
			$this->templateData['CONDITIONAL_PLAY_CHECKED'] = 'checked';

		/*
			'item_id' => $item_id,
			'title'   => $this->translator->translate('conditional_play_edit', 'playlists'),
			'html'    => $DlgTpl->get()
*/
	}

	public function render(): string
	{
		return $this->template->render('player/conditional', $this->templateData);
	}

	private function determineWeekdays(int $weekdays, array $weektimes): array
	{
		$listWeekdays = [];
		foreach($this->translator->translateArrayForOptions('weekday_selects', 'main') as $key => $inner_value)
		{
			$bin = pow(2, (int)$key - 1);
			$weekday = [
				'WEEKDAY_NUMBER' => $bin,
				'WEEKDAY' => $inner_value,
			];

			if (($bin & $weekdays) > 0)
			{
				$weekday['WEEK_DAY_CHECKED'] = 'checked';
				$this->conditionalPlay = true;
			}

			$from = 0;
			$until = 0;
			if (array_key_exists($bin, $weektimes))
			{
				$from = $weektimes[$bin]['from'];
				$until = $weektimes[$bin]['until'];
			}
			$weekday['HIDDEN_RANGE_FROM']  = $from;
			$weekday['HIDDEN_RANGE_UNTIL'] = $until;

			$listWeekdays[] = $weekday;
		}

		return $listWeekdays;
	}


}