export class PlaylistsSelector
{
	#itemsService = null;
	#playlistsService = null;

	constructor(itemsService, playlistsService)
	{
		this.#itemsService     = itemsService;
		this.#playlistsService = playlistsService;
	}

	async showSelector(element)
	{
		element.innerHTML = await this.#playlistsService.loadSelectorTemplate();

		//this.#treeViewWrapper.initTree();

	}
}