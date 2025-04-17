<?php
namespace App\Modules\Playlists\Helper\ExportSmil\items;


interface ItemInterface
{
    public function getPrefetch();
	public function getElement();
	public function getElementForPreview();
}
