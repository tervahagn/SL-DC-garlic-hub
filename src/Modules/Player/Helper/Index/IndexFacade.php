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


namespace App\Modules\Player\Helper\Index;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\Services\PlayerIndexService;

class IndexFacade
{
	private UserAgentHandler $userAgentHandler;
	private PlayerIndexService $playerService;


	public function parseUserAgent(string $userAgent): static
	{
		$this->userAgentHandler->parseUserAgent($userAgent);
		return $this;
	}

	public function fetchPlayerData(): static
	{
		$this->playerService->fetchDatabase($this->userAgentHandler->getUuid());
	}




	// 2. load Player Data

	// 3. select SMIL according to player status.

	// if there is player status 3 (with License)
	// 1. check for commands => generate Task scheduler
	// 2. send correct SMIL
	// 1. get the right SMIL depending on player model
	// 2. Build SMIL
	// 3. Write SMIL if it is different from previous stored
	// 4 send to player


}