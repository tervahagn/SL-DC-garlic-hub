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


namespace Tests\Unit\Modules\Mediapool\Services;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Modules\Mediapool\Repositories\FilesRepository;
use App\Modules\Mediapool\Repositories\NodesRepository;
use App\Modules\Mediapool\Services\AclValidator;
use App\Modules\Mediapool\Services\MediaService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MediaServiceTest extends TestCase
{

	private readonly MediaService $mediaService;
	private readonly FilesRepository&MockObject $mediaRepositoryMock;
	private readonly NodesRepository&MockObject $nodesRepositoryMock;
	private readonly AclValidator&MockObject $aclValidatorMock;
	private readonly Config&MockObject $configMock;
	private readonly LoggerInterface&MockObject $loggerMock;

	/**
	 * @throws Exception
	 * @throws CoreException
	 */
	protected function setUp(): void
	{
		$this->mediaRepositoryMock = $this->createMock(FilesRepository::class);
		$this->nodesRepositoryMock = $this->createMock(NodesRepository::class);
		$this->aclValidatorMock    = $this->createMock(AclValidator::class);
		$this->loggerMock          = $this->createMock(LoggerInterface::class);
		$this->configMock          = $this->createMock(Config::class);
		$this->aclValidatorMock->method('getConfig')->willReturn($this->configMock);
		$this->configMock->method('getConfigValue')->willReturnMap([
				['originals', 'mediapool', 'directories', 'original_directory'],
				['previews', 'mediapool', 'directories', 'preview_directory'],
				['thumbnails', 'mediapool', 'directories', 'thumbnail_directory'],
			]
		);

		$this->mediaService = new MediaService(
			$this->mediaRepositoryMock,
			$this->nodesRepositoryMock,
			$this->aclValidatorMock,
			$this->loggerMock
		);
		$this->mediaService->setUID(1);
	}

	#[Group('units')]
	public function testListMediaReturnsMediaList(): void
	{
		$nodeId = 1;
		$node = ['id' => $nodeId, 'name' => 'Test Node'];
		$mediaList = [
			['id' => 1, 'metadata' => json_encode(['key' => 'value']), 'checksum' => 'checksum', 'extension' => 'extension', 'thumb_extension' => 'thumb_extension'],
			['id' => 2, 'metadata' => json_encode(['key' => 'value']),'checksum' => 'checksum', 'extension' => 'extension', 'thumb_extension' => 'thumb_extension']
		];

		$this->nodesRepositoryMock->method('findNodeOwner')->willReturn($node);
		$this->aclValidatorMock->method('checkDirectoryPermissions')->willReturn(['read' => true]);
		$this->mediaRepositoryMock->method('findAllByNodeId')->willReturn($mediaList);
		$this->loggerMock->expects($this->never())->method('error');

		$result = $this->mediaService->listMedia($nodeId);

		$this->assertCount(2, $result);
		$this->assertEquals(['key' => 'value'], $result[0]['metadata']);
	}

	#[Group('units')]
	public function testListMediaNoReadPermissions(): void
	{
		$nodeId = 1;
		$node = ['id' => $nodeId, 'name' => 'Test Node'];

		$this->nodesRepositoryMock->method('findNodeOwner')->willReturn($node);
		$this->aclValidatorMock->method('checkDirectoryPermissions')->willReturn(['read' => false]);
		$this->loggerMock->expects($this->once())->method('error')
		->with('Error listing media: No read permissions on directory:Test Node');

		$this->assertEmpty($this->mediaService->listMedia($nodeId));
	}

	#[Group('units')]
	public function testFetchMediaReturnsMedia(): void
	{
		$mediaId = 'media-1';
		$media = [
			'id' => $mediaId,
			'metadata' => json_encode(['key' => 'value']),
			'node_id' => 1,
			'checksum' => 'checksum',
			'extension' => 'extension',
			'thumb_extension' => 'thumb_extension'
		];

		$this->mediaRepositoryMock->method('findAllWithOwnerById')->willReturn($media);
		$this->aclValidatorMock->method('checkDirectoryPermissions')->willReturn(['read' => true]);
		$this->loggerMock->expects($this->never())->method('error');

		$result = $this->mediaService->fetchMedia($mediaId);

		$this->assertEquals(['key' => 'value'], $result['metadata']);
	}

	#[Group('units')]
	public function testFetchMediaNoReadPermissions(): void
	{
		$mediaId = 'media-1';
		$media = ['id' => $mediaId, 'metadata' => json_encode(['key' => 'value']), 'node_id' => 1];

		$this->mediaRepositoryMock->method('findAllWithOwnerById')->willReturn($media);
		$this->aclValidatorMock->method('checkDirectoryPermissions')->willReturn(['read' => false]);
		$this->loggerMock->expects($this->once())->method('error')
			->with('Error fetch media: No read permissions in this directory: 1');

		$this->assertEmpty($this->mediaService->fetchMedia($mediaId));
	}

	#[Group('units')]
	public function testUpdateMediaUpdatesMedia(): void
	{
		$mediaId = 'media-1';
		$filename = 'new-filename';
		$description = 'new-description';
		$media = ['id' => $mediaId, 'node_id' => 1];

		$this->mediaRepositoryMock->method('findAllWithOwnerById')->willReturn($media);
		$this->aclValidatorMock->method('checkDirectoryPermissions')->willReturn(['edit' => true]);
		$this->mediaRepositoryMock->method('update')->willReturn(1);
		$this->loggerMock->expects($this->never())->method('error');

		$result = $this->mediaService->updateMedia($mediaId, $filename, $description);

		$this->assertEquals(1, $result);
	}

	#[Group('units')]
	public function testUpdateMediaNoEditPermissions()
	{
		$mediaId = 'media-1';
		$filename = 'new-filename';
		$description = 'new-description';
		$media = ['id' => $mediaId, 'node_id' => 2];

		$this->mediaRepositoryMock->method('findAllWithOwnerById')->willReturn($media);
		$this->aclValidatorMock->method('checkDirectoryPermissions')->willReturn(['edit' => false]);

		$this->loggerMock->expects($this->once())->method('error')
			->with('Error updating media: No edit permissions in this directory: 2');

		$this->assertEquals(0, $this->mediaService->updateMedia($mediaId, $filename, $description));
	}

	#[Group('units')]
	public function testDeleteMediaDeletesMedia()
	{
		$mediaId = 'media-1';
		$media = ['id' => $mediaId, 'node_id' => 1];

		$this->mediaRepositoryMock->method('findAllWithOwnerById')->willReturn($media);
		$this->aclValidatorMock->method('checkDirectoryPermissions')->willReturn(['edit' => true]);
		$this->mediaRepositoryMock->method('updateWithWhere')->willReturn(1);

		$result = $this->mediaService->deleteMedia($mediaId);

		$this->assertEquals(1, $result);
	}

	#[Group('units')]
	public function testDeleteMediaNoEditPermissionsThrowsException()
	{
		$mediaId = 'media-1';
		$media = ['id' => $mediaId, 'node_id' => 1];

		$this->mediaRepositoryMock->method('findAllWithOwnerById')->willReturn($media);
		$this->aclValidatorMock->method('checkDirectoryPermissions')->willReturn(['edit' => false]);

		$this->loggerMock->expects($this->once())->method('error')
			->with('Error deleting media: No edit permissions in this directory: 1');

		$this->assertEquals(0, $this->mediaService->deleteMedia($mediaId));
	}

	#[Group('units')]
	public function testMoveMediaMovesMedia()
	{
		$mediaId = 'media-1';
		$nodeId = 2;
		$media = ['id' => $mediaId, 'node_id' => 1];
		$node = ['id' => $nodeId, 'name' => 'New Node'];

		$this->mediaRepositoryMock->method('findAllWithOwnerById')->willReturn($media);
		$this->nodesRepositoryMock->method('findNodeOwner')->willReturn($node);
		$this->aclValidatorMock->method('checkDirectoryPermissions')->willReturnOnConsecutiveCalls(['read' => true], ['edit' => true]);
		$this->mediaRepositoryMock->method('updateWithWhere')->willReturn(1);
		$this->loggerMock->expects($this->never())->method('error');

		$result = $this->mediaService->moveMedia($mediaId, $nodeId);

		$this->assertEquals(1, $result);
	}

	#[Group('units')]
	public function testMoveMediaNoReadPermissions()
	{
		$mediaId = 'media-1';
		$nodeId = 2;
		$media = ['id' => $mediaId, 'node_id' => 1];

		$this->mediaRepositoryMock->method('findAllWithOwnerById')->willReturn($media);
		$this->aclValidatorMock->method('checkDirectoryPermissions')->willReturn(['read' => false]);

		$this->loggerMock->expects($this->once())->method('error')
			->with('Error moving media: No read permissions in this directory: 1');

		$this->mediaService->moveMedia($mediaId, $nodeId);
	}

	#[Group('units')]
	public function testMoveMediaNoEditPermissions()
	{
		$mediaId = 'media-1';
		$nodeId = 2;
		$media = ['id' => $mediaId, 'node_id' => 1];
		$node = ['id' => $nodeId, 'name' => 'New Node'];

		$this->mediaRepositoryMock->method('findAllWithOwnerById')->willReturn($media);
		$this->nodesRepositoryMock->method('findNodeOwner')->willReturn($node);

		$this->aclValidatorMock->method('checkDirectoryPermissions')
			->willReturnOnConsecutiveCalls(['read' => true], ['edit' => false]);

		$this->loggerMock->expects($this->once())->method('error')
			->with('Error moving media: No edit permissions in this directory: New Node');

		$this->mediaService->moveMedia($mediaId, $nodeId);
	}

	#[Group('units')]
	public function testCloneMediaClonesMedia()
	{
		$mediaId = 'media-1';
		$media = ['id' => $mediaId, 'node_id' => 1];
		$dataSet = ['id' => $mediaId, 'node_id' => 1, 'media_id' => 'new-media-id', 'UID' => 1];

		$this->mediaRepositoryMock->method('findAllWithOwnerById')->willReturn($media);
		$this->aclValidatorMock->method('checkDirectoryPermissions')->willReturn(['read' => true, 'edit' => true]);
		$this->mediaRepositoryMock->method('getFirstDataSet')->willReturn($dataSet);
		$this->mediaRepositoryMock->method('insert')->willReturn('new-media-id');
		$this->loggerMock->expects($this->never())->method('error');
		$this->loggerMock->expects($this->never())->method('error');

		$result = $this->mediaService->cloneMedia($mediaId);

		$this->assertEquals('new-media-id', $result['uuid']);
	}

	#[Group('units')]
	public function testCloneMediaNoPermissions()
	{
		$mediaId = 'media-1';
		$media = ['id' => $mediaId, 'node_id' => 1];

		$this->mediaRepositoryMock->method('findAllWithOwnerById')->willReturn($media);
		$this->aclValidatorMock->method('checkDirectoryPermissions')->willReturn(['read' => false, 'edit' => false]);

		$this->loggerMock->expects($this->once())->method('error')
			->with('Error cloning media: No permissions on this directory: 1');

		$this->mediaService->cloneMedia($mediaId);
	}

	#[Group('units')]
	public function testFetchMediaByChecksumReturnsMedia(): void
	{
		$checksum = 'checksum-123';
		$media = [
			'id' => 'media-1',
			'metadata' => json_encode(['key' => 'value']),
			'checksum' => $checksum,
			'extension' => 'jpg'
		];

		$this->mediaRepositoryMock->method('findAllWithOwnerByCheckSum')->willReturn($media);

		$result = $this->mediaService->fetchMediaByChecksum($checksum);

		$this->assertEquals(['key' => 'value'], $result['metadata']);
		$this->assertEquals($checksum, $result['checksum']);
	}

}
