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


namespace Tests\Unit\Modules\Users\Controller;

use App\Framework\Core\Session;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FormParameters\ScalarType;
use App\Modules\Users\Controller\UsersController;
use App\Modules\Users\Helper\Datatable\Parameters;
use App\Modules\Users\Services\UsersDatatableService;
use App\Modules\Users\UserStatus;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class UsersControllerTest extends TestCase
{
	private readonly ServerRequestInterface $requestMock;
	private readonly ResponseInterface $responseMock;
	private UsersDatatableService $usersServiceMock;
	private readonly Parameters $parametersMock;

	private StreamInterface $streamInterfaceMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->requestMock          = $this->createMock(ServerRequestInterface::class);
		$this->responseMock         = $this->createMock(ResponseInterface::class);
		$this->streamInterfaceMock  = $this->createMock(StreamInterface::class);

		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);
		$this->parametersMock = $this->createMock(Parameters::class);
		$this->usersServiceMock = $this->createMock(UsersDatatableService::class);

		$this->controller = new UsersController($this->usersServiceMock, $this->parametersMock);
	}

	/**
	 * @throws ModuleException
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFindByNameReturnsCorrectResponse(): void
	{
		$args = ['username' => 'testUser'];

		$sessionMock = $this->createMock(Session::class);
		$sessionMock->method('get')->with('user')->willReturn(['UID' => 123]);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($sessionMock);

		$this->parametersMock->expects($this->once())
			->method('addParameter')
			->with(Parameters::PARAMETER_FROM_STATUS, ScalarType::INT, UserStatus::REGISTERED->value);

		$this->parametersMock->expects($this->once())->method('setUserInputs')->with($args);
		$this->parametersMock->expects($this->once())->method('parseInputAllParameters');

		$this->usersServiceMock->expects($this->once())->method('setUID')->with(123);
		$this->usersServiceMock->expects($this->once())->method('loadUsersForOverview')->with($this->parametersMock);

		$this->usersServiceMock->method('getCurrentFilterResults')->willReturn([
			['UID' => 1, 'username' => 'JohnDoe'],
			['UID' => 2, 'username' => 'JaneDoe']
		]);

		$this->streamInterfaceMock->expects($this->once())->method('write')->with(json_encode([
			['id' => 1, 'name' => 'JohnDoe'],
			['id' => 2, 'name' => 'JaneDoe']
		]));

		$this->responseMock->expects($this->once())->method('withHeader')
			->with('Content-Type', 'application/json')
			->willReturnSelf();

		$this->responseMock->expects($this->once())->method('withStatus')->with(200)->willReturnSelf();

		$result = $this->controller->findByName($this->requestMock, $this->responseMock, $args);

		$this->assertSame($this->responseMock, $result);
	}
}
