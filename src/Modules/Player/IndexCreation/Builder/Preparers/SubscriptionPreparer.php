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


namespace App\Modules\Player\IndexCreation\Builder\Preparers;

class SubscriptionPreparer extends AbstractPreparer implements PreparerInterface
{
	public function prepare(): array
	{
		$subscriptions = [];

		if (!empty($this->playerEntity->getCommands()))
		{
			$subscriptions[] = $this->replaceTaskSchedule();
		}
		if ($this->playerEntity->getReports() > 0)
		{
			$reports = $this->playerEntity->getReports();
			if (isset($reports['inventory']))
				$subscriptions[] = $this->replaceReportInventory();
			if (isset($reports['play']))
				$subscriptions[] = $this->replaceReportPlayed();
			if (isset($reports['events']))
				$subscriptions[] = $this->replaceReportEvents();
			if (isset($reports['configuration']))
				$subscriptions[] = $this->replaceSystemConfiguration();
			if (isset($reports['executions']))
				$subscriptions[] = $this->replaceTasksExecutions();
		}
		return $subscriptions;
	}

	private function replaceTaskSchedule(): array
	{
		return [
			'SUBSCRIPTION_TYPE'   => 'TaskSchedule',
			'SUBSCRIPTION_ACTION' => $this->playerEntity->getIndexPath().'/task_scheduler.xml',
			'SUBSCRIPTION_METHOD' => 'get',
			'SUBSCRIPTION_RANDOM' => rand(1000, 9999)
		];
	}

	protected function replaceReportInventory(): array
	{
		return [
			'SUBSCRIPTION_TYPE'   => 'InventoryReport',
			'SUBSCRIPTION_ACTION' => $this->playerEntity->getReportServer() . '/inventory-' . $this->playerEntity->getUuid() . '.xml',
			'SUBSCRIPTION_METHOD' => 'put'
		];
	}

	protected function replaceReportPlayed(): array
	{
		return [
			'SUBSCRIPTION_TYPE', 'PlaylogCollection',
			'SUBSCRIPTION_ACTION', $this->playerEntity->getReportServer(),
			'SUBSCRIPTION_METHOD', 'put'
		];
	}


	protected function replaceReportEvents(): array
	{
		return [
			'SUBSCRIPTION_TYPE'   => 'EventlogCollection',
			'SUBSCRIPTION_ACTION' => $this->playerEntity->getReportServer(),
			'SUBSCRIPTION_METHOD' => 'put'
		];
	}

	protected function replaceSystemConfiguration(): array
	{
		return [
			'SUBSCRIPTION_TYPE'   => 'SystemReport',
			'SUBSCRIPTION_ACTION' => $this->playerEntity->getReportServer(). '/system-' . $this->playerEntity->getUuid().'.xml',
			'SUBSCRIPTION_METHOD' => 'put'
		];
	}

	/**
	 * @return $this
	 */
	protected function replaceTasksExecutions(): array
	{
		return [
			'SUBSCRIPTION_TYPE', 'TaskExecutionReport',
			'SUBSCRIPTION_ACTION', $this->playerEntity->getReportServer() . '/task_execution-' . $this->playerEntity->getUuid(). '.xml',
			'SUBSCRIPTION_METHOD', 'put'
		];
	}
}