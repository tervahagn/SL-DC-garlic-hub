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


namespace App\Framework\Dashboards;

use App\Framework\Core\SystemStats;
use App\Framework\Core\Translate\Translator;

/** works only on Linux */
class SystemDashboard implements DashboardInterface
{
	private readonly SystemStats $systemStats;
	private readonly Translator $translator;

	public function __construct(SystemStats $systemStats, Translator $translator)
	{
		$this->systemStats = $systemStats;
		$this->translator = $translator;
	}

	public function getId(): string
	{
		return 'system';
	}


	public function getTitle(): string
	{
		return $this->translator->translate('system_dashboard', 'main');
	}

	public function renderContent(): string
	{
		// TODO: Implement renderContent() method.
	}
}