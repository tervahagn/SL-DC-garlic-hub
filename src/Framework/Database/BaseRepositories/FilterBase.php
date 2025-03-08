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

namespace App\Framework\Database\BaseRepositories;

use Doctrine\DBAL\Exception;

abstract class FilterBase extends Sql
{
	use FindOperationsTrait;

	/**
	 * @throws Exception
	 */
	public function countAllFiltered(array $fields): int
	{
		$where = $this->prepareWhereForFiltering($fields);
		$join  = $this->prepareJoin();
		return $this->countAllBy($where, $join);
	}

	/**
	 * @throws Exception
	 */
	public function findAllFiltered(array $fields): array
	{
		$selects  = $this->prepareSelectFilteredForUser();
		$where 	  = $this->prepareWhereForFiltering($fields);
		$orderBy  = [$this->prepareOrderBy($fields)];
		$join     = $this->prepareJoin();
		$limit    = $this->determineLimit($fields['elements_page']['value'], $fields['elements_per_page']['value']);

		return $this->findAllByWithFields($selects, $where, $join, $limit, '', $orderBy);
	}

	/**
	 * @throws Exception
	 */
	public function countAllFilteredByUIDCompanyReseller(array $company_ids, array $search_fields, $user_id): int
	{
		$join  = $this->prepareJoin();
		$where  = $this->buildRestrictedWhereForCountAndFindSearch($company_ids, $search_fields, $user_id);
		return $this->countAllBy($where, $join);
	}

	/**
	 * @throws Exception
	 */
	public function findAllFilteredByUIDCompanyReseller(array $companyIds, array $fields, $UID): array
	{
		$selects = $this->prepareSelectFiltered();
		$where   = $this->buildRestrictedWhereForCountAndFindSearch($companyIds,  $fields, $UID);
		$join    = $this->prepareJoin();
		$orderBy = [$this->prepareOrderBy($fields)];
		$limit   = $this->determineLimit($fields['elements_page']['value'], $fields['elements_per_page']['value']);

		return $this->findAllByWithFields($selects, $where, $join, $limit, '', $orderBy);
	}

	/**
	 * @throws Exception
	 */
	public function countAllFilteredByUID(array $fields, $UID): int
	{
		$where = $this->prepareWhereForFiltering($fields);
		$where[$this->table.'.UID'] = $this->generateWhereClause($UID);

		return $this->countAllBy($where);
	}

	/**
	 * @throws Exception
	 */
	public function findAllFilteredByUID(array $fields, $UID): array
	{
		$selects = $this->prepareSelectFiltered();
		$where   = $this->prepareWhereForFiltering($fields);
		$where[$this->table.'.UID'] = $this->generateWhereClause($UID);
		$orderBy = [$this->prepareOrderBy($fields)];
		$limit   = $this->determineLimit($fields['elements_page']['value'], $fields['elements_per_page']['value']);

		return $this->findAllByWithFields($selects, $where, [], $limit, '', $orderBy);
	}

	private function buildRestrictedWhereForCountAndFindSearch(array $company_ids, array $search_fields, $UID): array
	{
		$where   = $this->prepareWhereForFiltering($search_fields);
		$where[] = [$this->table.'.UID' => $this->generateWhereClause($UID)];
		$where[] = $this->buildWhereByCompanyIds($company_ids);

		return $where;
	}


	abstract protected function prepareJoin();

	abstract protected function prepareSelectFiltered();

	abstract protected function prepareSelectFilteredForUser();

	protected function prepareOrderBy(array $fields, $useUserMain = true): array
	{
		// no sort column
		if (!array_key_exists('sort_column', $fields) || empty($fields['sort_column']) || !array_key_exists('value', $fields['sort_column']))
			return [];

		// validate ordering
		$sort_order = (array_key_exists('sort_order', $fields)) ? $fields['sort_order']['value'] : 'ASC';

		if (strcasecmp($sort_order, 'desc') != 0 && strcasecmp($sort_order, 'asc') != 0)
		{
			$sort_order = 'ASC';
		}

		// sort by user
		if ($fields['sort_column']['value'] == 'UID' ||
			$fields['sort_column']['value'] == 'usr_nickname')
		{
			$table = ($useUserMain === true) ?  'user_main.' : '';
			return ['sort' => $table . 'username ', 'order' => $sort_order];
		}

		return ['sort' => $this->table.'.'.$fields['sort_column']['value'], 'order' => $sort_order];
	}

	protected function buildWhereByCompanyIds(array $companyIds): array
	{
		if (!empty($companyIds))
		{
			return ['user_main.company_id' => $this->generateWhereClause(implode(',', $companyIds), 'IN', 'OR')];
		}

		return [];
	}

	protected function prepareWhereForFiltering(array $filterFields): array
	{
		$where             = array();
		foreach ($filterFields as $key => $parameter)
		{
			$clause = $this->determineWhereForFiltering($key, $parameter);
			if (!empty($clause))
				$where[] = $clause;
		}
		return $where;
	}
	protected function determineWhereForFiltering($key, $parameter): array
	{
		$where = [];
		switch ($key)
		{
			case 'elements_per_page':
			case 'elements_page':
			case 'sort_column':
			case 'sort_order':
				break;

			case 'UID':
			case 'username':
				if (empty($parameter['value']))
					break;

				$value = '%'.str_replace('*', '%', $parameter['value']).'%';
				$where['user_main.username'] = $this->generateWhereClause($value, 'LIKE');
				break;

			case 'company_id':
				if ((int) $parameter['value'] > 0)
				{
					$where['user_main.'.$key] = ['value' => $parameter['value'], 'operator' => '='];
				}
				break;

			default:
				if (empty($parameter['value']))
					break;
				$value = '%'.str_replace('*', '%', $parameter['value']).'%';
				$where[$this->table.'.'.$key] = $this->generateWhereClause($value, 'LIKE');
		}
		return $where;
	}

}