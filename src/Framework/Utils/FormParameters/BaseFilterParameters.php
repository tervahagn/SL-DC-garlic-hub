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

/**
 * The BaseFilterParameters class serves as an abstract foundational class for defining and managing
 * filtering parameters, specifically tailored for handling user requests. It includes functionalities
 * that involve parsing and storing parameters in session, ensuring parameters are structured
 * correctly, and facilitating parameter-related operations such as sorting and pagination used in datatables.
 *
 * This class is designed to be used within larger systems that require a consistent approach
 * to managing and applying filter parameters. The implementation supports flexible configuration
 * for default parameter values and ensures customizable behavior for extending classes.
 *
 */
abstract class BaseFilterParameters extends BaseParameters implements BaseFilterParametersInterface
{
	protected readonly string $sessionStoreKey;
	protected readonly Session $session;

	protected array $defaultParameters = array(
		self::PARAMETER_ELEMENTS_PER_PAGE  => array('scalar_type'  => ScalarType::INT,       'default_value' => 10,    'parsed' => false),
		self::PARAMETER_ELEMENTS_PAGE      => array('scalar_type'  => ScalarType::INT,       'default_value' => 1,     'parsed' => false),
		self::PARAMETER_SORT_COLUMN        => array('scalar_type'  => ScalarType::STRING,    'default_value' => '',    'parsed' => false),
		self::PARAMETER_SORT_ORDER         => array('scalar_type'  => ScalarType::STRING,    'default_value' => 'ASC', 'parsed' => false)
	);

	public function __construct(string $moduleName, Sanitizer $sanitizer, Session $session, string $session_key_store = '')
	{
		$this->session           = $session;
		$this->sessionStoreKey   = $session_key_store;

		parent::__construct($moduleName, $sanitizer);
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
	 * checks if parameters are stored in session from previous visit
	 * iterates over all parameters and sets the values
	 *
	 * @throws  ModuleException
	 */
	public function parseInputFilterAllUsers(): static
	{
		if ($this->storedParametersInSessionExists())
			$this->currentParameters = $this->getStoredSearchParamsFromSession();

		$this->parseInputAllParameters();

		$this->storeSearchParamsToSession($this->currentParameters);

		return $this;
	}

	public function hasSessionKeyStore(): bool
	{
		return !empty($this->sessionStoreKey);
	}


	public function addCompany(): void
	{
		$this->addParameter(self::PARAMETER_COMPANY_ID, ScalarType::STRING, '');
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