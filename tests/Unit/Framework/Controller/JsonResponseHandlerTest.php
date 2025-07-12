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


namespace Tests\Unit\Framework\Controller;

use App\Framework\Controller\JsonResponseHandler;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class JsonResponseHandlerTest extends TestCase
{
	private ResponseInterface&MockObject $responseMock;
	private StreamInterface&MockObject $streamInterfaceMock;
	private JsonResponseHandler $jsonResponseHandler;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->responseMock        = $this->createMock(ResponseInterface::class);
		$this->streamInterfaceMock = $this->createMock(StreamInterface::class);
		$this->jsonResponseHandler = new JsonResponseHandler();
	}


	#[Group('units')]
	public function testJsonResponseCorrect(): void
	{
		$data = ['anotherKey' => 'anotherValue'];
		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);
		$this->streamInterfaceMock->method('write')->with(json_encode($data));
		$this->responseMock->expects($this->once())->method('withHeader')
			->with('Content-Type', 'application/json')
			->willReturnSelf();
		$this->responseMock->expects($this->once())->method('withStatus')->with('200');

		$this->jsonResponseHandler->jsonResponse($this->responseMock, $data);

	}

	#[Group('units')]
	public function testJsonSuccessWithoutAdditionalData(): void
	{
		$data = ['success' => true];
		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);
		$this->streamInterfaceMock->method('write')->with(json_encode($data));
		$this->responseMock->expects($this->once())->method('withHeader')
			->with('Content-Type', 'application/json')
			->willReturnSelf();
		$this->responseMock->expects($this->once())->method('withStatus')->with(200);

		$this->jsonResponseHandler->jsonSuccess($this->responseMock);
	}

	#[Group('units')]
	public function testJsonSuccessWithAdditionalData(): void
	{
		$additionalData = ['anotherKey' => 'anotherValue'];
		$data = ['success' => true] + $additionalData;

		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);
		$this->streamInterfaceMock->method('write')->with(json_encode($data));
		$this->responseMock->expects($this->once())->method('withHeader')
			->with('Content-Type', 'application/json')
			->willReturnSelf();
		$this->responseMock->expects($this->once())->method('withStatus')->with(200);

		$this->jsonResponseHandler->jsonSuccess($this->responseMock, $additionalData);
	}

	#[Group('units')]
	public function testJsonErrorWithDefaultStatus(): void
	{
		$errorMessage = 'An error occurred';

		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);
		$this->streamInterfaceMock->method('write')->with(json_encode(['success' => false, 'error_message' => $errorMessage]));
		$this->responseMock->expects($this->once())->method('withHeader')
			->with('Content-Type', 'application/json')
			->willReturnSelf();
		$this->responseMock->expects($this->once())->method('withStatus')->with(400);

		$this->jsonResponseHandler->jsonError($this->responseMock, $errorMessage);
	}

	#[Group('units')]
	public function testJsonErrorWithCustomStatus(): void
	{
		$errorMessage = 'Custom error';
		$customStatus = 500;

		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);
		$this->streamInterfaceMock->method('write')->with(json_encode(['success' => false, 'error_message' => $errorMessage]));
		$this->responseMock->expects($this->once())->method('withHeader')
			->with('Content-Type', 'application/json')
			->willReturnSelf();
		$this->responseMock->expects($this->once())->method('withStatus')->with($customStatus);

		$this->jsonResponseHandler->jsonError($this->responseMock, $errorMessage, $customStatus);
	}
}
