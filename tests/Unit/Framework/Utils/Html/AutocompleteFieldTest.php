<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Utils\Html\AutocompleteField;
use App\Framework\Utils\Html\FieldType;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class AutocompleteFieldTest extends TestCase
{
	private AutocompleteField $autocompleteField;

	protected function setUp(): void
	{
		$attributes = [
			'id' => 'username',
			'type' => FieldType::AUTOCOMPLETE,
			'name' => 'user_name',
			'value' => 'defaultUser',
			'default_value' => 'guest',
			'rules' => ['required' => true],
			'data-label' => 'test-label'
		];

		$this->autocompleteField = new AutocompleteField($attributes);
	}

	#[Group('units')]
	public function testGetDataLabelReturnsExpectedValue(): void
	{
		$this->assertSame('test-label', $this->autocompleteField->getDataLabel());
	}
}
