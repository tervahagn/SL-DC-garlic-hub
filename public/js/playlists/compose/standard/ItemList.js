export default class ItemList
{
	#itemFactory = null;
	#dropTarget = null;
	#itemsService = null;
	#itemsList   = {};

	constructor(itemFactory, itemsService, dropTarget)
	{
		this.#itemFactory  = itemFactory;
		this.#dropTarget   = dropTarget;
		this.#itemsService = itemsService;
	}

	async displayPlaylist(playlistId)
	{
		const results = await this.#itemsService.loadByPlaylistId(playlistId);

		for (const item of results.list.items)
		{
			this.createPlaylistItem(item);
		}
	}

	createPlaylistItem(itemData)
	{
		const item = this.#itemFactory.create(itemData);
		this.#itemsList[itemData.item_id] = item;
		this.#dropTarget.appendChild(item.buildItemElement());
		this.createActions(item);
	}

	createActions(item)
	{
		if (item.deleteItemAction !== null)
		{
			item.deleteItemAction.addEventListener('click', () => {
				const id = item.deleteItemAction.parentElement.getAttribute('data-item-id');
				document.getElementById("itemId-" + id).remove();
			});
		}
	}
}