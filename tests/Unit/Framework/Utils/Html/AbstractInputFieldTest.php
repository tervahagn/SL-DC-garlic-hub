<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Utils\Html\AbstractInputField;
use App\Framework\Utils\Html\FieldType;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ConcreteInputField extends AbstractInputField
{

}

class AbstractInputFieldTest extends TestCase
{

	#[Group('units')]
	public function testConstructorWithValidAttributes(): void
	{
		$field = new ConcreteInputField([
			'id' => 'custom_id',
			'type' => FieldType::TEXT,
			'name' => 'custom_name',
			'title' => 'Custom Title',
			'label' => 'Custom Label',
			'value' => 'Custom Value',
			'default_value' => 'Default Value',
			'attributes' => ['attr1' => 'value1'],
			'rules' => ['required' => true]
		]);

		$this->assertSame('custom_id', $field->getId());
		$this->assertSame(FieldType::TEXT, $field->getType());
		$this->assertSame('custom_name', $field->getName());
		$this->assertSame('Custom Title', $field->getTitle());
		$this->assertSame('Custom Label', $field->getLabel());
		$this->assertSame('Custom Value', $field->getValue());
		$this->assertSame(['attr1' => 'value1'], $field->getAttributes());
		$this->assertSame(['required' => true], $field->getValidationRules());
	}


}
