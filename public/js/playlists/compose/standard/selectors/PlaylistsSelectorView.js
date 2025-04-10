export class SelectorView
{
	#items = {};

	constructor()
	{
	}

	get items()
	{
		return this.#items;
	}

	getPlaylistsListElement()
	{
		// because cache can prevent to execute
		return document.getElementById("playlistsList");
	}

	displayList(dataList)
	{
		const mediaListElement = document.getElementById("playlistsList");
		playlistsListElement.innerHTML = "";

		for (const playlist of dataList)
		{
			let media = this.#mediaFactory.create(mediaData);
			this.#items[media.mediaId] = media;
			mediaListElement.appendChild(media.renderSimple());
		}
	}

}