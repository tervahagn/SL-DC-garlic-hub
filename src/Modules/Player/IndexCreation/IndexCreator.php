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


namespace App\Modules\Player\IndexCreation;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\TemplateEngine\AdapterInterface;
use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\IndexCreation\Builder\TemplatePreparer;
use App\Modules\Playlists\Collector\Builder\PlaylistBuilderFactory;

class IndexCreator
{
	private PlaylistBuilderFactory $playlistBuilderFactory;
	private IndexTemplateSelector $templateSelector;
	private IndexFile $indexFile;
	private TemplatePreparer $templatePreparer;
	private AdapterInterface $templateService;

	private string $indexFilePath = '';

	public function __construct(PlaylistBuilderFactory $playlistBuilderFactory,
		IndexTemplateSelector $templateSelector,
		IndexFile $indexFile,
		TemplatePreparer $templatePreparer,
		AdapterInterface $templateService)
	{
		$this->playlistBuilderFactory = $playlistBuilderFactory;
		$this->templateSelector = $templateSelector;
		$this->indexFile = $indexFile;
		$this->templatePreparer = $templatePreparer;
		$this->templateService = $templateService;
	}

	public function createForReleasedPlayer(PlayerEntity $playerEntity, Config $config): void
	{
		$this->indexFilePath = $config->getConfigValue('path_smil_index', 'player').
			'/'.$playerEntity->getUuid().'/'.$playerEntity->getPlaylistId().'.smil';

		$playlistStructure = $this->playlistBuilderFactory->createBuilder($playerEntity)->buildPlaylist();
		$indexTemplate     = $this->templateSelector->select($playerEntity);

		$templateData = $this->templatePreparer
			->setPlayerEntity($playerEntity)
			->setPlaylistStructure($playlistStructure)
			->prepare($indexTemplate)
			->getTemplateData()
		;


		$smilIndex = $this->templateService->render('player/index/'.$indexTemplate->value, $templateData);

		$this->indexFile->setIndexFilePath($this->indexFilePath)->handleIndexFile($smilIndex);

	}

	public function getIndexFilePath(): string
	{
		return $this->indexFilePath;
	}
}