<?php

namespace App\Framework\Database;

class QueryBuilder
{
	public function buildSelectQuery(string $fields, string $table, string $where = '', string $join = '', string $limit = '', string $groupBy = '', string $orderBy = ''): string
	{
		$whereClause = $where ? 'WHERE ' . $where : '';
		$joinClause = $join ? $join : '';
		$limitClause = $limit ? 'LIMIT ' . $limit : '';
		$groupByClause = $groupBy ? 'GROUP BY ' . $groupBy : '';
		$orderByClause = $orderBy ? 'ORDER BY ' . $orderBy : '';

		return sprintf("SELECT %s FROM %s %s %s %s %s %s", $fields, $table, $joinClause, $whereClause, $groupByClause, $orderByClause, $limitClause);
	}

	public function buildInsertQuery(string $table, array $data): string
	{
		$fields = implode(', ', array_keys($data));
		$values = implode(', ', array_map(fn($value) => "'" . addslashes($value) . "'", $data));

		return sprintf("INSERT INTO %s (%s) VALUES (%s)", $table, $fields, $values);
	}

	public function buildUpdateQuery(string $table, array $data, string $where): string
	{
		$setClause = implode(', ', array_map(fn($key, $value) => sprintf("%s='%s'", $key, addslashes($value)), array_keys($data), $data));
		return sprintf("UPDATE %s SET %s WHERE %s", $table, $setClause, $where);
	}

	public function buildDeleteQuery(string $table, string $where, string $limit = ''): string
	{
		$limitClause = $limit ? 'LIMIT ' . $limit : '';
		return sprintf("DELETE FROM %s WHERE %s %s", $table, $where, $limitClause);
	}

	public function buildLimitClause(int $limit_start = 0, int $limit_show = 0): string
	{
		if ($limit_start == 0)
			$limit_start = 1;

		if ($limit_show > 0)
			$limit = ($limit_start - 1) * $limit_show . ',' . $limit_show;
		 else
			$limit = '';

		return $limit ? 'LIMIT ' . $limit : '';
	}

	/**
	 * Builds a WHERE clause for company IDs.
	 *
	 * @param array $ar_company_ids Company IDs
	 * @return string WHERE clause
	 */
	public function buildWhereByCompanyIds(array $ar_company_ids): string
	{
		if (count($ar_company_ids) > 0)
			return ' OR user_main.company_id IN (' . implode(',', $ar_company_ids) . ')';

		return '';
	}

	/**
	 * Builds a WHERE clause for element IDs.
	 *
	 * @param array $ar_element_ids Element IDs
	 * @return string WHERE clause
	 */
	public function buildWhereByElementIds(string $id_field, array $ar_element_ids): string
	{
		if (count($ar_element_ids) > 0)
			return ' OR ' . $id_field . ' IN (' . implode(',', $ar_element_ids) . ')';

		return '';
	}

	/**
	 * Prepares the LIMIT clause for a query.
	 *
	 * @param array $ar_search_fields Search fields
	 * @return string LIMIT clause
	 */
	public function prepareLimit(array $ar_search_fields): string
	{
		return $this->buildLimitClause($ar_search_fields['elements_page']['value'], $ar_search_fields['elements_per_page']['value']);
	}

	/**
	 * Prepares the ORDER BY clause for a query.
	 *
	 * @param array $ar_search_fields Search fields
	 * @param bool $uid_use_main_user_table Use main user table for UID
	 * @return string ORDER BY clause
	 */
	public function prepareOrderBy(string $table, array $ar_search_fields, bool $uid_use_main_user_table = true): string
	{
		if (!array_key_exists('sort_column', $ar_search_fields) || empty($ar_search_fields['sort_column']) || !array_key_exists('value', $ar_search_fields['sort_column']))
			return '';

		$sort_order = (array_key_exists('sort_order', $ar_search_fields)) ? $ar_search_fields['sort_order']['value'] : 'ASC';

		if (strcasecmp($sort_order, 'desc') != 0 && strcasecmp($sort_order, 'asc') != 0)
		{
			$sort_order = 'ASC';
		}

		if ($ar_search_fields['sort_column']['value'] == 'UID' ||
			$ar_search_fields['sort_column']['value'] == 'usr_nickname')
		{
			$table = ($uid_use_main_user_table === true) ? 'user_main.' : '';
			return $table . 'usr_nickname '.$sort_order;
		}

		return $table().'.'.$ar_search_fields['sort_column']['value'].' '.$sort_order;
	}



}
