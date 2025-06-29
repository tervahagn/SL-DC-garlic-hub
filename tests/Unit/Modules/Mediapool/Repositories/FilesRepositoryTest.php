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


namespace Tests\Unit\Modules\Mediapool\Repositories;

use App\Modules\Mediapool\Repositories\FilesRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilesRepositoryTest extends TestCase
{
	private Connection&MockObject $connectionMock;
	private QueryBuilder&MockObject $queryBuilderMock;
	private FilesRepository $filesRepository;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->connectionMock = $this->createMock(Connection::class);
		$this->queryBuilderMock  = $this->createMock(QueryBuilder::class);
		$this->filesRepository = new FilesRepository($this->connectionMock);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllWithOwnerById(): void
	{
		$result = $this->createMock(Result::class);

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')->willReturn($this->queryBuilderMock );

		$this->queryBuilderMock ->expects($this->once())
			->method('select')
			->with('user_main.username, company_id, media_id, mediapool_files.UID, node_id, upload_time, checksum, mimetype, metadata, tags, filename, extension, thumb_extension, media_description, config_data')
			->willReturnSelf();

		$this->queryBuilderMock ->expects($this->once())
			->method('from')
			->with('mediapool_files')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->exactly(2))
			->method('andWhere')
			->willReturnCallback(function ($condition) {
				$expectedConditions = ['media_id = :media_id', 'deleted = :deleted'];
				$this->assertContains($condition, $expectedConditions);
				return $this->queryBuilderMock;
			});
		$this->queryBuilderMock->expects($this->exactly(2))
			->method('setParameter')
			->willReturnCallback(function ($name, $value) {
				$expectedNames = ['media_id', 'deleted'];
				$expectedValues = ['123', 0];
				$this->assertContains($name, $expectedNames);
				$this->assertContains($value, $expectedValues);
				return $this->queryBuilderMock;
			});

		$this->queryBuilderMock ->expects($this->once())
			->method('leftJoin')
			->with('mediapool_files', 'user_main', 'user_main', 'user_main.UID=mediapool_files.UID')
			->willReturnSelf();

		$this->queryBuilderMock ->expects($this->once())->method('executeQuery')->willReturn($result);

		$result->expects($this->once())
			->method('fetchAllAssociative')
			->willReturn([['username' => 'testuser', 'media_id' => '123']]);

		// Call the method and assert the result
		$result = $this->filesRepository->findAllWithOwnerById('123');
		$this->assertEquals(['username' => 'testuser', 'media_id' => '123'], $result);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllByNodeId(): void
	{
		$resultMock = $this->createMock(Result::class);

		// Set up expectations for the QueryBuilder
		$this->connectionMock->expects($this->once())
			->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock );

		$this->queryBuilderMock ->expects($this->once())
			->method('select')
			->with('user_main.username, company_id, media_id, node_id, mediapool_files.UID, upload_time, checksum, mimetype, metadata, tags, filename, extension, thumb_extension, media_description')
			->willReturnSelf();

		$this->queryBuilderMock ->expects($this->once())
			->method('from')
			->with('mediapool_files')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->exactly(2))
			->method('andWhere')
			->willReturnCallback(function ($condition) {
				$expectedConditions = ['node_id = :node_id', 'deleted = :deleted'];
				$this->assertContains($condition, $expectedConditions);
				return $this->queryBuilderMock;
			});
		$this->queryBuilderMock->expects($this->exactly(2))
			->method('setParameter')
			->willReturnCallback(function ($name, $value) {
				$expectedNames = ['node_id', 'deleted'];
				$expectedValues = [456, 0];
				$this->assertContains($name, $expectedNames);
				$this->assertContains($value, $expectedValues);
				return $this->queryBuilderMock;
			});

		$this->queryBuilderMock ->expects($this->once())
			->method('leftJoin')
			->with('mediapool_files', 'user_main', 'user_main', 'user_main.UID=mediapool_files.UID')
			->willReturnSelf();

		$this->queryBuilderMock ->expects($this->once())
			->method('addOrderBy')
			->with('upload_time', 'DESC')
			->willReturnSelf();

		$this->queryBuilderMock ->expects($this->once())
			->method('executeQuery')
			->willReturn($resultMock);

		$resultMock->expects($this->once())
			->method('fetchAllAssociative')
			->willReturn([['username' => 'testuser', 'node_id' => 456]]);

		// Call the method and assert the result
		$result = $this->filesRepository->findAllByNodeId(456);
		$this->assertEquals([['username' => 'testuser', 'node_id' => 456]], $result);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllWithOwnerByCheckSum(): void
	{
		$resultMock = $this->createMock(Result::class);

		// Set up expectations for the QueryBuilder
		$this->connectionMock->expects($this->once())
			->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())
			->method('select')
			->with('user_main.username, company_id, media_id, mediapool_files.UID, node_id, upload_time, checksum, mimetype, metadata, tags, filename, extension, thumb_extension, media_description, config_data')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())
			->method('from')
			->with('mediapool_files')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())
			->method('andWhere')
			->with('checksum = :checksum')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())
			->method('setParameter')
			->with('checksum', 'test-checksum')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())
			->method('leftJoin')
			->with('mediapool_files', 'user_main', 'user_main', 'user_main.UID=mediapool_files.UID')
			->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())
			->method('executeQuery')
			->willReturn($resultMock);

		$resultMock->expects($this->once())
			->method('fetchAllAssociative')
			->willReturn([['username' => 'testuser', 'checksum' => 'test-checksum']]);

		// Call the method and assert the result
		$result = $this->filesRepository->findAllWithOwnerByCheckSum('test-checksum');
		$this->assertEquals(['username' => 'testuser', 'checksum' => 'test-checksum'], $result);
	}
}