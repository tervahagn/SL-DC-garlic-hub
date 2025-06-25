<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Utils\Html\DropdownField;
use App\Framework\Utils\Html\FieldType;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class DropdownFieldTest extends TestCase
{
	private DropdownField $dropdownField;

	protected function setUp(): void
	{
		$attributes = [
			'id' => 'dropdown1',
			'type' => FieldType::DROPDOWN,
			'name' => 'dropdown_field',
			'options' => ['Option1' => [], 'Option2' => [], 'Option3' => []]
		];

		$this->dropdownField = new DropdownField($attributes);
	}

	#[Group('units')]
	public function testGetOptionsReturnsCorrectOptions(): void
	{
		$this->assertSame(['Option1' => [], 'Option2' => [], 'Option3' => []], $this->dropdownField->getOptions());
	}
}
