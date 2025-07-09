<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);


namespace Tests\Unit\Modules\Users\Helper\InitialAdmin;

use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Utils\FormParameters\ScalarType;
use App\Modules\Users\Helper\InitialAdmin\Parameters;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ParametersTest extends TestCase
{
	private Parameters $parameters;

	/**
	 * @throws Exception|\PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$sanitizerMock = $this->createMock(Sanitizer::class);
		$sessionMock = $this->createMock(Session::class);

		$this->parameters = new Parameters($sanitizerMock, $sessionMock);
	}
	#[Group('units')]
	public function testConstructorInitializesCurrentParametersCorrectly(): void
	{
		$expectedParameters = [
			Parameters::PARAMETER_ADMIN_NAME => [
				'scalar_type' => ScalarType::STRING,
				'default_value' => '',
				'parsed' => false,
			],
			Parameters::PARAMETER_ADMIN_EMAIL => [
				'scalar_type' => ScalarType::STRING,
				'default_value' => '',
				'parsed' => false,
			],
			Parameters::PARAMETER_ADMIN_LOCALE => [
				'scalar_type' => ScalarType::STRING,
				'default_value' => 'en_US',
				'parsed' => false,
			],
			Parameters::PARAMETER_ADMIN_PASSWORD => [
				'scalar_type' => ScalarType::STRING,
				'default_value' => '',
				'parsed' => false,
			],
			Parameters::PARAMETER_ADMIN_PASSWORD_CONFIRM => [
				'scalar_type' => ScalarType::STRING,
				'default_value' => '',
				'parsed' => false,
			],
		];

		static::assertSame($expectedParameters, array_intersect_key(
			$this->parameters->getCurrentParameters(),
			$expectedParameters
		));
	}

	#[Group('units')]
	public function testConstructorSetsModuleNameToUser(): void
	{
		static::assertSame('user', $this->parameters->getModuleName());
	}
}
