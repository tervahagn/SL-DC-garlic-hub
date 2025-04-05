<?php

namespace App\Modules\Playlists\Helper;

enum ItemType: string
{
	case MEDIA = 'media';
	case MEDIA_EXTERN = 'media_url';
	case PLAYLIST = 'playlist';
	case PLAYLIST_EXTERN = 'playlist_url';
	case TEMPLATE = 'template';
	case CHANNEL = 'channel';
}