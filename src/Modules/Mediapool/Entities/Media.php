<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

namespace App\Modules\Mediapool\Entities;

use App\Framework\BaseRepositories\Sql;
use App\Framework\Database\DBHandler;
use App\Framework\Database\Helpers\DataPreparer;
use App\Framework\Database\QueryBuilder;


class Media extends Sql
{
	/**
	 * values for column "mediatype"
	 */
	const MEDIA_TYPE_DOCUMENT        = 'document';
	const MEDIA_TYPE_PDFVIEW         = 'pdfview';
	const MEDIA_TYPE_WIDGET          = 'widget';
	const MEDIA_TYPE_DOWNLOAD        = 'download';
	const MEDIA_TYPE_DATASOURCE      = 'datasource';
	const MEDIA_TYPE_IMAGE           = 'image';
	const MEDIA_TYPE_VIDEO           = 'video';
	const MEDIA_TYPE_AUDIO           = 'audio';
	const MEDIA_TYPE_TEXT_HTML       = 'text_html';
	const MEDIA_TYPE_IMAGE_AND_VIDEO = 'image_and_video';
	const MEDIA_TYPE_FILE            = 'file';
	const MEDIA_TYPE_PLAYLIST        = 'smil_playlist';

	private const VALID_MEDIA_TYPES = [
		self::MEDIA_TYPE_DOWNLOAD,
		self::MEDIA_TYPE_WIDGET,
		self::MEDIA_TYPE_DOCUMENT,
		self::MEDIA_TYPE_AUDIO,
		self::MEDIA_TYPE_IMAGE,
		self::MEDIA_TYPE_TEXT_HTML,
		self::MEDIA_TYPE_VIDEO
	];

	private const SELECT_FIELDS = 'UID, username, media_id, node_id, filename, filesize, duration, mediatype, filetype, media_description, company_id';


	public function __construct(DBHandler $dbh, QueryBuilder $queryBuilder, DataPreparer $dataPreparer, string $table, string $id_field)
	{
		parent::__construct($dbh, $queryBuilder, $dataPreparer, 'mediapool_media', 'media_id');
	}

	/**
	 * @param int $node_id
	 *
	 * @return  array
	 */
	public function findAllByNodeId(int $node_id): array
	{
		$join     = 'LEFT JOIN user_main USING(UID)';
		$where    = 'node_id = '.$node_id.' AND deleted = 0 ';
		$order_by = 'media_id DESC';

		return $this->findAllByWithFields(self::SELECT_FIELDS, $where, $join, '', '', $order_by);
	}

	/**
	 * @param array $media_ids
	 *
	 * @return array
	 */
	public function findByMediaIds(array $media_ids): array
	{
		if (empty($media_ids))
			return array();

		$select = 'media_id, node_id, filename, filesize, duration, mediatype, filetype';
		$where  = 'media_id IN('.implode(',', $media_ids).')';

		return $this->findAllByWithFields($select, $where);
	}


	/**
	 * @param int    $node_id
	 * @param string $media_type
	 *
	 * @return array
	 */
	public function findAllByNodeIdAndMediaType(int $node_id, string $media_type): array
	{
		$join     = 'LEFT JOIN user_main USING(UID)';
		$where    = 'node_id = '.$node_id.' AND deleted = 0'.$this->buildWhereFileTypeByMediaType($media_type);
		$order_by = 'media_id DESC';
		return $this->findAllByWithFields(self::SELECT_FIELDS, $where, $join, '', '', $order_by);
	}

	/**
	 * @param string $search_field
	 * @param int    $media_id
	 *
	 * @return string
	 */
	public function findOneFieldByMediaId(string $search_field, int $media_id): string
	{
		return $this->findOneValueBy($search_field, 'media_id = '.$media_id);
	}

	/**
	 * @param int $media_id
	 *
	 * @return  array
	 */
	public function findByIdWithJoinedNodeData(int $media_id): array
	{
		$join   = 'LEFT JOIN media_nodes USING(node_id)
					LEFT JOIN user_main ON user_main.UID = media_nodes.UID';
		$select = 'media_nodes.UID, node_id, media_id, parent_id, is_public, domain_ids, filetype, filename, filesize, mediatype, company_id';
		$where  = $this->id_field.' = '.$media_id;
		$result = $this->findAllByWithFields($select, $where, $join, 1);

		return $this->getFirstDataSet($result);
	}

	/**
	 * this is used for rebuilding previews of all media
	 *
	 * @return array
	 */
	public function findAllNotDeleted(): array
	{
		$where = 'deleted = 0';
		return $this->findAllBy($where);
	}

	/**
	 * @param int $node_id
	 *
	 * @return int
	 */
	public function markMediaDeletedByNodeId(int $node_id): int
	{
		$update = array('deleted' => 1);
		$where  = 'node_id = '.$node_id;

		return $this->updateWithWhere($update, $where);
	}

	/**
	 * returns a part of where clause, depending on given frontend filter ($media_type)
	 *
	 * @param string $media_type
	 *
	 * @return  string
	 */
	protected function buildWhereFileTypeByMediaType(string $media_type): string
	{
		if (empty($media_type))
			return '';

		return match ($media_type)
		{
			self::MEDIA_TYPE_DOCUMENT         => " AND mediatype = 'document'",
			self::MEDIA_TYPE_PDFVIEW          => " AND filetype = 'pdf'",
			self::MEDIA_TYPE_IMAGE            => " AND mediatype = 'image'",
			self::MEDIA_TYPE_VIDEO            => " AND mediatype = 'video'",
			self::MEDIA_TYPE_AUDIO            => " AND mediatype = 'audio'",
			self::MEDIA_TYPE_WIDGET           => " AND mediatype = 'widget'",
			self::MEDIA_TYPE_DOWNLOAD,
			self::MEDIA_TYPE_FILE             => " AND mediatype = 'download'",
			self::MEDIA_TYPE_DATASOURCE       => " AND mediatype = 'datasource'",
			self::MEDIA_TYPE_PLAYLIST         => " AND mediatype IN('image', 'video', 'widget', 'audio')",
			self::MEDIA_TYPE_IMAGE_AND_VIDEO  => " AND mediatype IN('image', 'video')",
			default => '',
		};
	}

}