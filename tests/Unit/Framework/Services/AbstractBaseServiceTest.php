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

namespace Tests\Unit\Framework\Services;

use App\Framework\Services\AbstractBaseService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ConcreteService extends AbstractBaseService
{
	public function addSomeErrors(): void
	{
		$this->addErrorMessage('This is an Error');
	}

	public function getUID(): int
	{
		return $this->UID;
	}
}
class AbstractBaseServiceTest extends TestCase
{
	private ConcreteService $service;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$loggerMock = $this->createMock(LoggerInterface::class);
		$this->service = new ConcreteService($loggerMock);
	}

	#[Group('units')]
	public function testErrorMessages(): void
	{
		$this->assertFalse($this->service->hasErrorMessages());
		$this->assertEmpty($this->service->getErrorMessages());

		$this->service->addSomeErrors();

		$this->assertTrue($this->service->hasErrorMessages());
		$this->assertSame(['This is an Error'], $this->service->getErrorMessages());
	}

	#[Group('units')]
	public function testSetUID(): void
	{
		$this->service->setUID(123);

		$this->assertSame(123, $this->service->getUID());
	}


}
