export default class ItemList
{
	#itemFactory = null;
	#dropTarget = null;
	#itemsService = null;
	#itemList   = {};

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
		this.#itemList[itemData.item_id] = item;
		this.#dropTarget.appendChild(item.buildItemElement());
	}
}