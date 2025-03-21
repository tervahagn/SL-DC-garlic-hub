<?php
namespace App\Framework\Services;

/**
 * trait to store the filter/search parameters
 */
trait SearchFilterParamsTrait
{
	protected array $currentFilterParams = [];
	protected int $currentTotalResult = 0;
	protected array $allowedCompanyIds = [];
	protected array $currentFilterResults = [];
	protected array $companies = [];

	public function setCurrentFilterParams(array $ar_search): static
	{
		$this->currentFilterParams = $ar_search;
		return $this;
	}

	public function setCurrentTotalResult(int $total_result): static
	{
		$this->currentTotalResult = $total_result;
		return $this;
	}

	public function getCurrentTotalResult(): int
	{
		return $this->currentTotalResult;
	}

	public function setCompanyArray(array $ar_companies): static
	{
		$this->companies = $ar_companies;
		return $this;
	}

	public function getCompanyArray(): array
	{
		return $this->companies;
	}

	/**
	 * expects array in form of array(1, 2, 3, 4, 5...x)
	 */
	public function setAllowedCompanyIds(array $ar_company_ids): static
	{
		$this->allowedCompanyIds = $ar_company_ids;
		return $this;
	}

	public function getAllowedCompanyIds(): array
	{
		return $this->allowedCompanyIds;
	}

	public function setCurrentFilterResults(array $ar_result): static
	{
		$this->currentFilterResults = $ar_result;
		return $this;
	}

	public function getCurrentFilterResults(): array
	{
		return $this->currentFilterResults;
	}

	public function returnFilteredDomainsArrayForCheckBoxes(): array
	{
		$domains = [];
// Todo: Find a solution for getUser which is here userEntity;
/*		foreach($this->getUser()->getDomainsArray() as $key => $value)
		{
			if (in_array($value['company_id'], $this->getAllowedCompanyIds()))
			{
				$domains[$key] = $value;
			}
		}
*/
		return $domains;
	}

	/**
	 * this method adds an all (-) for dropdowns at first position
	 * If you don't need this, call ::returnFilteredDomainsArray()
	 *
	 * the return array is
	 * - key: domain_id
	 * - value: domain_name
	 *
	 * NOT usable for createBinaryDomainCheckboxGroup()
	 *
	 */
	public function returnFilteredCompaniesForDropdowns(): array
	{
		// need domain_id as key, not value
		$allowed_ids       = array_flip($this->getAllowedCompanyIds());
		$allowed_companies = array_intersect_key($this->getCompanyArray(), $allowed_ids);

		// array_merge re-numerate keys+
		// look at https://www.php.net/manual/de/function.array-merge.php
		// we need
		return array('-') + $allowed_companies;
	}

	/**
	 * this is basically the same as above (including of unshifted '-' value), but here we are using
	 * the key as pow(2, domain_id). Bit values for comparing AND with our stored value in DB
	 *
	 * example:
	 * array ( 2 => 'first company', 4 => 'second company', 8 => 'third company',...)
	 */
	public function returnFilteredDomainsForDropdowns(): array
	{
		// need domain_id as key, not value
		$allowed_ids       = array_flip($this->getAllowedCompanyIds());
		$allowed_companies = array_intersect_key($this->getCompanyArray(), $allowed_ids);

		$domain_ids = array_map( function($value) { return pow(2, $value); }, $this->getAllowedCompanyIds());

		// notice: we can not use array_merge() because this will renumber any numeric key
		return array('-') + array_combine($domain_ids, $allowed_companies);
	}

	protected function setAllResultData(int $total, array $results): static
	{
		return $this->setCurrentTotalResult($total)
					->setCurrentFilterResults($results);
	}
}
