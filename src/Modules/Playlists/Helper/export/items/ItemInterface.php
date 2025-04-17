<?php
namespace App\Modules\Playlists\Helper\export\items;


interface ItemInterface
{
    public function getPrefetch();
	public function getElement();
	public function getElementForPreview();
}
