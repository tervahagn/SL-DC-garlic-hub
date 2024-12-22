<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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


namespace App\Modules\Mediapool;

use Doctrine\DBAL\Exception;

class NodesService
{
	private NodesRepository $nodesRepository;

	public function __construct(NodesRepository $nodesRepository)
	{
		$this->nodesRepository = $nodesRepository;
	}

	/**
	 * @throws Exception
	 */
	public function getNodes(int $parent_id): array
	{
		if ($parent_id === 0)
			$node_data = $this->nodesRepository->findAllRootNodes();
		else
			$node_data =  $this->nodesRepository->findAllChildNodesByParentNode($parent_id);

		$nodes = [];
		foreach ($node_data as $node)
		{
			$nodes[] = $this->prepareForWunderbaum($node);
		}

		return $nodes;
	}

	protected function prepareForWunderbaum(array $node_data): array
	{
		return array(
			'title'         => $node_data['name'],
			'folder'        => true,
			'key'           => $node_data['node_id'],
			'lazy'          => ($node_data['children'] > 0),
			'create_sub'    => true,
			'edit_node'		=> true,
			'delete_node'	=> true,
			'UID'			=> $node_data['UID'],
			'is_public'		=> $node_data['is_public'],
			'domain_ids'	=> $node_data['domain_ids']
		);
	}
}