export class SelectorView
{
	#mediaFactory = null;
	#dragItem = null;
	#mediaList = null;

	#isDragDrop = true;
	#dropTarget = null;

	constructor(mediaFactory)
	{
		this.#mediaFactory = mediaFactory;
	}

	set dropTarget(value)
	{
		this.#dropTarget = value;
	}

	set isDragDrop(value)
	{
		this.#isDragDrop = value;
	}

	displayMediaList(mediaDataList)
	{
		this.#mediaFactory.mediaTemplateElement = document.getElementById("mediaTemplate");
		this.#mediaList = document.getElementById("mediaList");
		this.#mediaList.innerHTML = "";
		this.#dropTarget = document.getElementById("thePlaylist");

		for (const mediaData of mediaDataList)
		{
			let media = this.#mediaFactory.create();
			let mediaItem = media.buildMediaItem(mediaData);

			this.#mediaList.appendChild(mediaItem);

			if (this.#isDragDrop === true)
				this.#prepareDragDrop(mediaItem);
		}
	}

	#prepareDragDrop(mediaItem)
	{
		mediaItem.addEventListener("dragstart", (event) => {
			this.#dragItem = event.target;
			event.dataTransfer.effectAllowed = 'move';
		});
		this.#dropTarget.addEventListener('dragover', (event) => {
			event.preventDefault();
		});
		this.#dropTarget.addEventListener('drop', (event) => {
			event.preventDefault();
			this.#dropTarget.appendChild(this.#dragItem);
			this.#dragItem = null;
		});
	}
}