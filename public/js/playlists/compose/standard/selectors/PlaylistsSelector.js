export class PlaylistsSelector
{
	#playlistsService = null;

	constructor(playlistsService)
	{
		this.#playlistsService = playlistsService;
	}

	async showSelector(element)
	{
		element.innerHTML = await this.#playlistsService.loadSelectorTemplate();

		this.#playlistsService.loadInternalPlaylists();
	}
}