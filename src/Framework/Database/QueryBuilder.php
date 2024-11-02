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

namespace App\Framework\Database;

/**
 * Constructs SQL queries for CRUD operations with optional clauses.
 */
class QueryBuilder
{
	/**
	 * Builds a SELECT query with optional WHERE, JOIN, LIMIT, GROUP BY, and ORDER BY clauses.
	 *
	 * @param string $fields Fields to select
	 * @param string $table Table name
	 * @param string $where Optional WHERE clause
	 * @param string $join Optional JOIN clause
	 * @param string $limit Optional LIMIT clause
	 * @param string $groupBy Optional GROUP BY clause
	 * @param string $orderBy Optional ORDER BY clause
	 * @return string The complete SELECT query
	 */
	public function buildSelectQuery(string $fields, string $table, string $where = '', string $join = '', string $limit = '', string $groupBy = '', string $orderBy = ''): string
	{
		$whereClause = $where ? 'WHERE ' . $where : '';
		$joinClause = $join ? : '';
		$limitClause = $limit ? 'LIMIT ' . $limit : '';
		$groupByClause = $groupBy ? 'GROUP BY ' . $groupBy : '';
		$orderByClause = $orderBy ? 'ORDER BY ' . $orderBy : '';

		return sprintf("SELECT %s FROM %s %s %s %s %s %s", $fields, $table, $joinClause, $whereClause, $groupByClause, $orderByClause, $limitClause);
	}

	/**
	 * Builds an INSERT query.
	 *
	 * @param string $table Table name
	 * @param array $data Associative array of field-value pairs
	 * @return string The complete INSERT query
	 */
	public function buildInsertQuery(string $table, array $data): string
	{
		$fields = implode(', ', array_keys($data));
		$values = implode(', ', array_map(fn($value) => "'" . addslashes($value) . "'", $data));

		return sprintf("INSERT INTO %s (%s) VALUES (%s)", $table, $fields, $values);
	}

	/**
	 * Builds an UPDATE query with a WHERE clause.
	 *
	 * @param string $table Table name
	 * @param array $data Associative array of field-value pairs to update
	 * @param string $where WHERE clause
	 * @return string The complete UPDATE query
	 */
	public function buildUpdateQuery(string $table, array $data, string $where): string
	{
		$setClause = implode(', ', array_map(fn($key, $value) => sprintf("%s='%s'", $key, addslashes($value)), array_keys($data), $data));
		return sprintf("UPDATE %s SET %s WHERE %s", $table, $setClause, $where);
	}

	/**
	 * Builds a DELETE query with an optional LIMIT clause.
	 *
	 * @param string $table Table name
	 * @param string $where WHERE clause
	 * @param string $limit Optional LIMIT clause
	 * @return string The complete DELETE query
	 */
	public function buildDeleteQuery(string $table, string $where, string $limit = ''): string
	{
		$limitClause = $limit ? 'LIMIT ' . $limit : '';
		return sprintf("DELETE FROM %s WHERE %s %s", $table, $where, $limitClause);
	}

	/**
	 * Builds a LIMIT clause based on start and count.
	 *
	 * @param int $limit_start Start position
	 * @param int $limit_show Number of records to show
	 * @return string The LIMIT clause
	 */
	public function buildLimitClause(int $limit_start = 0, int $limit_show = 0): string
	{
		if ($limit_start == 0) $limit_start = 1;
		$limit = ($limit_show > 0) ? (($limit_start - 1) * $limit_show . ',' . $limit_show) : '';

		return $limit ? 'LIMIT ' . $limit : '';
	}

	/**
	 * Builds a WHERE clause for specified company IDs.
	 *
	 * @param array $ar_company_ids List of company IDs
	 * @return string WHERE clause
	 */
	public function buildWhereByCompanyIds(array $ar_company_ids): string
	{
		return count($ar_company_ids) > 0 ? ' OR user_main.company_id IN (' . implode(',', $ar_company_ids) . ')' : '';
	}

	/**
	 * Builds a WHERE clause for specified element IDs.
	 *
	 * @param string $id_field Field name for IDs
	 * @param array $ar_element_ids List of element IDs
	 * @return string WHERE clause
	 */
	public function buildWhereByElementIds(string $id_field, array $ar_element_ids): string
	{
		return count($ar_element_ids) > 0 ? ' OR ' . $id_field . ' IN (' . implode(',', $ar_element_ids) . ')' : '';
	}

	/**
	 * Prepares a LIMIT clause from search fields.
	 *
	 * @param array $ar_search_fields Search field parameters
	 * @return string LIMIT clause
	 */
	public function prepareLimit(array $ar_search_fields): string
	{
		return $this->buildLimitClause($ar_search_fields['elements_page']['value'], $ar_search_fields['elements_per_page']['value']);
	}

	/**
	 * Prepares an ORDER BY clause based on search fields.
	 *
	 * @param string $table Table name
	 * @param array $ar_search_fields Search field parameters
	 * @param bool $uid_use_main_user_table Use main user table for UID
	 * @return string ORDER BY clause
	 */
	public function prepareOrderBy(string $table, array $ar_search_fields, bool $uid_use_main_user_table = true): string
	{
		if (!array_key_exists('sort_column', $ar_search_fields) || empty($ar_search_fields['sort_column']) || !array_key_exists('value', $ar_search_fields['sort_column']))
			return '';

		$sort_order = $ar_search_fields['sort_order']['value'] ?? 'ASC';
		$sort_order = in_array(strtoupper($sort_order), ['ASC', 'DESC']) ? $sort_order : 'ASC';

		if (in_array($ar_search_fields['sort_column']['value'], ['UID', 'usr_nickname'])) {
			$table = $uid_use_main_user_table ? 'user_main.' : '';
			return $table . 'usr_nickname ' . $sort_order;
		}

		return $table . '.' . $ar_search_fields['sort_column']['value'] . ' ' . $sort_order;
	}
}
