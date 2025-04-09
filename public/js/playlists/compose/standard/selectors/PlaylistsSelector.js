export class PlaylistsSelector
{
	#itemsService = null;
	#playlistsService = null;

	constructor(itemsService, playlistsService)
	{
		this.#itemsService     = itemsService;
		this.#playlistsService = playlistsService;
	}


}