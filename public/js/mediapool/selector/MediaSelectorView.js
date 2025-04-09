export class MediaSelectorView
{
	#mediaFactory = null;
	#mediaItems = {};

	constructor(mediaFactory)
	{
		this.#mediaFactory = mediaFactory;
	}

	get mediaItems()
	{
		return this.#mediaItems;
	}

	getMediaListElement()
	{
		// because cache can prevent to execute displayMediaList
		return document.getElementById("mediaList");
	}

	displayMediaList(mediaDataList)
	{
		const mediaListElement = document.getElementById("mediaList");
		mediaListElement.innerHTML = "";

		for (const mediaData of mediaDataList)
		{
			let media = this.#mediaFactory.create(mediaData);
			this.#mediaItems[media.mediaId] = media;
			mediaListElement.appendChild(media.renderSimple());
		}
	}

}