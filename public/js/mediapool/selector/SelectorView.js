export class SelectorView
{
	#mediaFactory = null;
	#mediaListElement = null;
	#mediaItems = {};

	constructor(mediaFactory)
	{
		this.#mediaFactory = mediaFactory;
	}

	get mediaItems()
	{
		return this.#mediaItems;
	}

	get mediaListElement()
	{
		return this.#mediaListElement;
	}

	displayMediaList(mediaDataList)
	{
		this.#mediaListElement = document.getElementById("mediaList");
		this.#mediaListElement.innerHTML = "";

		for (const mediaData of mediaDataList)
		{
			let media = this.#mediaFactory.create(mediaData);
			this.#mediaItems[media.mediaId] = media;
			this.#mediaListElement.appendChild(media.renderSimple());
		}
	}

}