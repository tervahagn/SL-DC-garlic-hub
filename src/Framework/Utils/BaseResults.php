<?php
namespace App\Framework\Utils;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

abstract class BaseResults
{
	protected BaseParameters $InputFilterParameter;
	protected array $tplData = [];
	protected string $site = '';
	protected Translator $Translator;
	protected array $tableHeaderFields = [];
	protected int $currentTotalResult = 0;
	protected array $currentFilterResults = [];
	protected array $additionalUrlParameters = [];
	protected array $languageModules = [];

	public function getInputFilterParameter(): BaseParameters
	{
		return $this->InputFilterParameter;
	}

	public function setInputFilterParameter(BaseParameters $InputFilterParameter): static
	{
		$this->InputFilterParameter = $InputFilterParameter;
		return $this;
	}

	public function getSite(): string
	{
		return $this->site;
	}

	public function setSite(string $site): static
	{
		$this->site = $site;
		return $this;
	}

	public function getTplData(): array
	{
		return $this->tplData;
	}

	public function getTranslator(): Translator
	{
		return $this->Translator;
	}

	public function setTranslator(Translator $Translator): static
	{
		$this->Translator = $Translator;
		return $this;
	}
	public function getCurrentTotalResult(): int
	{
		return $this->currentTotalResult;
	}

	public function setCurrentTotalResult(int $currentTotalResult): static
	{
		$this->currentTotalResult = $currentTotalResult;
		return $this;
	}

	public function getCurrentFilterResults(): array
	{
		return $this->currentFilterResults;
	}

	public function setCurrentFilterResults(array $currentFilterResults): static
	{
		$this->currentFilterResults = $currentFilterResults;
		return $this;
	}

	public function addAdditionalUrlParameter(string $key, string|int $value): static
	{
		$this->additionalUrlParameters[$key] = $value;
		return $this;
	}

	public function clearAdditionalUrlParameters()
	{
		$this->additionalUrlParameters = array();
		return $this;
	}

	public function hasAdditionalUrlParameters(): bool
	{
		return (count($this->additionalUrlParameters) > 0);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function renderTableHeader(BaseParameters $InputFilterParameter, $site, Translator $translate): static
	{
		$this->setInputFilterParameter($InputFilterParameter)
			 ->setSite($site)
			 ->setTranslator($translate);

		$header = [];
		/* @var $HeaderField HeaderField */
		foreach($this->tableHeaderFields as $HeaderField)
		{
			$headerFieldName = $HeaderField->getName();
			$controlName     = ['CONTROL_NAME' => [$headerFieldName]];
			if ($HeaderField->isSortable())
			{
				$controlName[$headerFieldName][] = ['if_sortable' => $this->renderSortableHeaderField($HeaderField)];
			}
			else
			{
				$controlName[$headerFieldName][] = ['LANG_CONTROL_NAME_2' => $this->renderNonSortableHeaderField($HeaderField)];
			}

			$header[] = $controlName;
		}

		$this->tplData['elements_result_header'] = $header;

		return $this;
	}

	public function getTableHeaderFields(): array
	{
		return $this->tableHeaderFields;
	}

	public function createField(): HeaderField
	{
		$field = new HeaderField();
		$this->addField($field);
		return $field;
	}

	public function addLanguageModule($moduleName): static
	{
		$this->languageModules[] = $moduleName;
		return $this;
	}

	protected function addField(HeaderField $field): static
	{
		$this->tableHeaderFields[] = $field;
		return $this;
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	protected function renderSortableHeaderField(HeaderField $headerField):array
	{

		$sortableData = array();

		if ($this->getInputFilterParameter()->getValueOfParameter('sort_column') == $headerField->getName())
		{
			if ($this->getInputFilterParameter()->getValueOfParameter('sort_order') == 'asc')
			{
				$sort_order_tmp = 'desc';
				$sortableData['SORTABLE_ORDER']    = '▼';
			}
			else
			{
				$sort_order_tmp = 'asc';
				$sortableData['SORTABLE_ORDER']    = '▲';
			}
		}
		else
		{
			$sort_order_tmp = 'asc';
			$sortableData['SORTABLE_ORDER'] = '◆';
		}

		// todo: find a solution for _Mainsite
		$sortableData['SORT_CONTROL_NAME']         = $headerField->getName();
		$sortableData['LINK_CONTROL_SORT_ORDER']   = '_Mainsite' . '?' . $this->buildSortUrl($headerField, $sort_order_tmp);
		$sortableData['LANG_CONTROL_NAME']         = $this->translate($headerField);

		return $sortableData;
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	protected function renderNonSortableHeaderField(HeaderField $headerField): string
	{
		return $this->translate($headerField);
	}

	protected function buildSortUrl(HeaderField $headerField, string $sort_order): string
	{
		$params = array(
			'site'              => $this->getSite(),
			'elements_page'     => $this->getInputFilterParameter()->getValueOfParameter('elements_page'),
			'sort_column'       => $headerField->getName(),
			'sort_order'        => $sort_order,
			'elements_per_page' => $this->getInputFilterParameter()->getValueOfParameter('elements_per_page')
		);

		if ($this->hasAdditionalUrlParameters())
		{
			$params = array_merge($params, $this->additionalUrlParameters);
		}

		return http_build_query($params);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	protected function translate(HeaderField $HeaderField): string
	{
		if ($HeaderField->shouldSkipTranslation())
			return '';

		$key  = $HeaderField->getName();

		if ($HeaderField->hasSpecificLangModule())
		{
			return $this->getTranslator()->translate($key, $HeaderField->getSpecificLanguageModule());
		}
		else
		{
			foreach($this->languageModules as $module)
			{
				try
				{
					$translated = $this->getTranslator()->translate($key, $module);
					if (!empty($translated))
					{
						return $translated;
					}
				}
				catch(FrameworkException $e) { }
			}
		}

		return '';
	}
}