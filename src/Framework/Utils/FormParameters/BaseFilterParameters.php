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

namespace App\Framework\Utils\FormParameters;

use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Exceptions\ModuleException;

class BaseFilterParameters extends BaseParameters
{
	public const string PARAMETER_ELEMENTS_PER_PAGE = 'elements_per_page';
	public const string PARAMETER_ELEMENTS_PAGE     = 'elements_page';
	public const string PARAMETER_SORT_COLUMN       = 'sort_column';
	public const string PARAMETER_SORT_ORDER        = 'sort_order';
	public const string DEFAULT_SORT_ORDER          = 'ASC';
	protected readonly string $sessionStoreKey;

	protected array $defaultParameters = array(
		self::PARAMETER_ELEMENTS_PER_PAGE  => array('scalar_type'  => ScalarType::INT,       'default_value' => 10,              'parsed' => false),
		self::PARAMETER_ELEMENTS_PAGE      => array('scalar_type'  => ScalarType::INT,       'default_value' => 1,            'parsed' => false),
		self::PARAMETER_SORT_COLUMN        => array('scalar_type'  => ScalarType::STRING,    'default_value' => '',            'parsed' => false),
		self::PARAMETER_SORT_ORDER         => array('scalar_type'  => ScalarType::STRING,    'default_value' => self::DEFAULT_SORT_ORDER, 'parsed' => false)
	);

	public function __construct(string $moduleName, Sanitizer $sanitizer, Session $session, string $session_key_store = '')
	{
		parent::__construct($moduleName, $sanitizer, $session);
		$this->sessionStoreKey   = $session_key_store;
	}

	/**
	 * @throws ModuleException
	 */
	public function setParameterDefaultValues($default_sort_column): static
	{
		$this->setDefaultForParameter(self::PARAMETER_SORT_COLUMN, $default_sort_column);
		return $this;
	}

	/**
	 * since we are using ELEMENTS_PAGE and ELEMENTS_PER_PAGE for the limit clause in MySQL
	 * this method sets both values to 0 (zero).
	 * That means, there will be no LIMIT clause in the SQL query
	 *
	 * @throws ModuleException
	 */
	public function setElementsParametersToNull(): static
	{
		if ($this->hasParameter(self::PARAMETER_ELEMENTS_PAGE))
		{
			$this->setValueOfParameter(self::PARAMETER_ELEMENTS_PAGE, 0);
		}

		if ($this->hasParameter(self::PARAMETER_ELEMENTS_PER_PAGE))
		{
			$this->setValueOfParameter(self::PARAMETER_ELEMENTS_PER_PAGE, 0);
		}

		return $this;
	}


	/**
	 * - checks if parameters are stored in session from previous visit
	 * - iterates over all parameters and sets the values
	 * - handles session storage by calling trait
	 *
	 * @throws  ModuleException
	 */
	public function parseInputFilterAllUsers(bool $filter_submitted = false): static
	{
		if ($filter_submitted === false && $this->storedParametersInSessionExists())
		{
			$this->currentParameters = $this->getStoredSearchParamsFromSession();
		}
		else
		{
			$this->parseInputAllParameters();
		}

		if ($filter_submitted === true)
		{
			$this->storeSearchParamsToSession($this->currentParameters);
		}

		return $this;
	}


	public function hasSessionKeyStore(): bool
	{
		return !empty($this->sessionStoreKey);
	}

	protected function storeSearchParamsToSession(array $ar_search): static
	{
		if ($this->hasSessionKeyStore())
		{
			$this->session->set($this->sessionStoreKey, $ar_search);
		}
		return $this;
	}

	protected function storedParametersInSessionExists(): bool
	{
		if ($this->hasSessionKeyStore())
		{
			return ($this->session->exists($this->sessionStoreKey));
		}
		return false;
	}

	/**
	 * @throws ModuleException
	 */
	protected function getStoredSearchParamsFromSession(): string|array|null
	{
		if ($this->storedParametersInSessionExists())
		{
			return $this->session->get($this->sessionStoreKey);
		}
		throw new ModuleException($this->moduleName, 'Can not find key ' . $this->sessionStoreKey . ' in session store');
	}

}