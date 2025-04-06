export default class ItemList
{
	#itemFactory = null;
	#dropTarget = null;
	#itemsService = null;
	#itemList   = [];

	constructor(itemFactory, itemsService, dropTarget)
	{
		this.#itemFactory  = itemFactory;
		this.#dropTarget   = dropTarget;
		this.#itemsService = itemsService;
	}

	async displayPlaylist(playlistId)
	{
		const results = await this.#itemsService.loadItemsByPlaylistId(playlistId);

		for (const item of results.list.items)
		{
			this.createPlaylistItem(item);
		}
	}

	/**
	 *
	 * @param item
	 */
	createPlaylistItem(item)
	{
		const template = document.getElementById("playlistItemTemplate");
		const playlistItem = template.content.cloneNode(true);

		const listItem = playlistItem.querySelector('.playlist-item');
		listItem.dataset.mediaId = item.file_resource;

		const thumbnail = playlistItem.querySelector('img');
		const thumbnailUrl = "/" + item.paths.thumbnail.replace('public/', '');
		thumbnail.src = thumbnailUrl;
		thumbnail.alt = item.item_name;

		const itemName = playlistItem.querySelector('.item-name');
		itemName.textContent = item.item_name;

		const itemDuration = playlistItem.querySelector('.item-duration');
		itemDuration.textContent = item.item_duration;

		this.#dropTarget.appendChild(playlistItem);
	}
}