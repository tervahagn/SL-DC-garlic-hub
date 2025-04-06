export class Item
{
	#itemData = null
	#playlistItem = null;

	#deleteAction = null;

	constructor(itemData)
	{
		this.#itemData = itemData;
	}

	get deleteAction()
	{
		return this.#deleteAction;
	}

	buildItemElement()
	{
		const template = document.getElementById("playlistItemTemplate");
		const playlistItem = template.content.cloneNode(true);
		this.#playlistItem = playlistItem;

		const listItem = playlistItem.querySelector('.playlist-item');
		listItem.dataset.mediaId = this.#itemData.file_resource;

		const thumbnail = playlistItem.querySelector('img');
		thumbnail.src = "/" + this.#itemData.paths.thumbnail.replace('public/', '');
		thumbnail.alt = this.#itemData.item_name;

		const itemName = playlistItem.querySelector('.item-name');
		itemName.textContent = this.#itemData.item_name;

		const itemDuration = playlistItem.querySelector('.item-duration');
		itemDuration.textContent = this.#formatSecondsToTime();

		this.#createActions();

		return playlistItem;
	}

	#createActions()
	{

		this.#playlistItem.querySelector('.link-playlist').remove();

		this.#playlistItem.querySelector('.template-edit').remove();

		this.#playlistItem.querySelector('.conditional-play').remove();
		this.#playlistItem.querySelector('.trigger-edit').remove();

		this.#playlistItem.querySelector('.settings-edit').remove();

		if (this.#itemData.mimetype !== "application/widget")
			this.#playlistItem.querySelector('.widget-edit').remove();

		this.#playlistItem.querySelector('.copy-item').remove();

		this.#deleteAction = this.#playlistItem.querySelector('.delete-item');
		this.#deleteAction.setAttribute('data-delete-id', this.#itemData.item_id)




	}

	#formatSecondsToTime()
	{
		const seconds = this.#itemData.item_duration
		const hours = Math.floor(seconds / 3600);
		const minutes = Math.floor((seconds % 3600) / 60);
		const secs = seconds % 60;

		const pad = (num) => String(num).padStart(2, '0');

		return `${pad(hours)}:${pad(minutes)}:${pad(secs)}`;
	}


}