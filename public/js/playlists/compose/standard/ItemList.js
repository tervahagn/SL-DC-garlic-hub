export default class ItemList
{
	#itemFactory = null;
	#dropTarget = null;
	#itemsService = null;
	#itemsList   = {};
	#playlistId = 0;
	#playlistDuration = document.getElementById('playlistDuration');
	#totalItems       = document.getElementById('totalItems');
	#totalFilesize    = document.getElementById('totalFilesize');

	constructor(itemFactory, itemsService, dropTarget)
	{
		this.#itemFactory  = itemFactory;
		this.#dropTarget   = dropTarget;
		this.#itemsService = itemsService;
	}

	async displayPlaylist(playlistId)
	{
		this.#playlistId = playlistId;
		const results = await this.#itemsService.loadByPlaylistId(playlistId);
		if (!results.success)
			return;

		for (const item of results.data.items)
		{
			this.createPlaylistItem(item);
		}
		this.displayPlaylistProperties(results.data.playlist)
	}

	createPlaylistItem(itemData)
	{
		const item = this.#itemFactory.create(itemData);
		this.#itemsList[itemData.item_id] = item;
		this.#dropTarget.appendChild(item.buildItemElement());
		this.#createActions(item);
	}

	displayPlaylistProperties(playlistProperties)
	{
		this.#playlistDuration.innerHTML = playlistProperties.duration;
		this.#totalItems.innerHTML       = playlistProperties.count_items;
		this.#totalFilesize.innerHTML    = playlistProperties.filesize;
		// properties.owner_duration;
	}


	#createActions(item)
	{
		if (item.deleteItemAction !== null)
		{
			item.deleteItemAction.addEventListener('click', async () => {
				const id = item.deleteItemAction.parentElement.getAttribute('data-item-id');
				const results =  await this.#itemsService.delete(this.#playlistId, id);
				if (!results.success)
					return;

				this.displayPlaylistProperties(results.data.playlist)

				document.getElementById("itemId-" + id).remove();
			});
		}
	}

}