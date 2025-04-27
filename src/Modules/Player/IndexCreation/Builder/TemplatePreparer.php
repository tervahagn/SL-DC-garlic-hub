<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


namespace App\Modules\Player\IndexCreation\Builder;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\Enums\IndexSections;
use App\Modules\Player\Enums\TemplateIndexFiles;
use App\Modules\Player\IndexCreation\Builder\Preparers\PreparerFactory;
use App\Modules\Playlists\Collector\Contracts\PlaylistStructureInterface;

class TemplatePreparer
{
	private PlayerEntity $playerEntity;
	private PlaylistStructureInterface $playlistStructure;
	private readonly PreparerFactory $preparerFactory;

	private array $meta = [];
	private array $subscriptions = [];
	private array $layout = [];
	private array $standby_times = [];
	private array $playlist = [];

	private array $templateData;

	public function __construct(PreparerFactory $preparerFactory)
	{
		$this->preparerFactory = $preparerFactory;
	}

	public function setPlayerEntity(PlayerEntity $playerEntity): static
	{
		$this->playerEntity = $playerEntity;
		return $this;
	}

	public function setPlaylistStructure(PlaylistStructureInterface $playlistStructure): static
	{
		$this->playlistStructure = $playlistStructure;
		return $this;
	}

	public function getTemplateData(): array
	{
		return $this->templateData;
	}

	public function prepare(TemplateIndexFiles $indexFile): static
	{
		switch ($indexFile)
		{
			case TemplateIndexFiles::GARLIC:
				$this->prepareForGarlic();
				break;
			case TemplateIndexFiles::XMP2XXX:
				$this->prepareForIAdea();
				break;
			case TemplateIndexFiles::SIMPLE:
				$this->prepareForSimple();
				break;
		}
		return $this;
	}

	private function prepareForGarlic(): void
	{
		$this->prepareStandards();
		$this->subscriptions = $this->preparerFactory->create(IndexSections::SUBSCRIPTIONS, $this->playerEntity)->prepare();
		$this->layout        = $this->preparerFactory->create(IndexSections::LAYOUT, $this->playerEntity)->prepare();
		$this->standby_times = $this->preparerFactory->create(IndexSections::STANDBY_TIMES, $this->playerEntity)->prepare();
		$playlist            = $this->preparerFactory->create(IndexSections::PLAYLIST, $this->playerEntity);

		$playlist->setPlaylistStructure($this->playlistStructure)->setIsSimple(false);
		$this->playlist = $playlist->prepare();
		$this->setTemplateData();
	}

	private function prepareForIAdea(): void
	{
		$this->prepareForGarlic();
	}

	private function prepareForSimple(): void
	{
		$this->prepareStandards();

		$playlist =	$this->preparerFactory->create(IndexSections::PLAYLIST, $this->playerEntity);
		$playlist->setPlaylistStructure($this->playlistStructure)->setIsSimple(true);
		$this->playlist = $playlist->prepare();
		$this->setTemplateDataSimple();
	}

	private function prepareStandards(): void
	{
		$this->meta   = $this->preparerFactory->create(IndexSections::META, $this->playerEntity)->prepare();
		$this->layout = $this->preparerFactory->create(IndexSections::LAYOUT, $this->playerEntity)->prepare();
	}

	private function setTemplateDataSimple(): void
	{
		$this->templateData = [
			'meta' => $this->meta,
			'subscriptions' => $this->subscriptions,
			'layout' => $this->layout,
			'standby_times' => $this->standby_times,
			'playlist' => $this->playlist,
		];
	}

	private function setTemplateData(): void
	{
		$this->templateData = [
			'meta' => $this->meta,
			'layout' => $this->layout,
			'playlist' => $this->playlist,
		];
	}
}