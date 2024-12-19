<?php

namespace Tests\Unit\Framework\Utils\Html;

use App\Framework\Utils\Html\FieldInterface;
use App\Framework\Utils\Html\FieldsRenderFactory;
use App\Framework\Utils\Html\TextField;
use App\Framework\Utils\Html\TextRenderer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class FieldsRenderFactoryTest extends TestCase
{
	private FieldsRenderFactory $fieldsRenderFactory;

	protected function setUp(): void
	{
		$this->fieldsRenderFactory = new FieldsRenderFactory();
	}


	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testGetRendererForUnsupportedField(): void
	{
		$fieldMock = $this->createMock(FieldInterface::class);

		$this->expectException(InvalidArgumentException::class);

		$this->fieldsRenderFactory->getRenderer($fieldMock);
	}
}
