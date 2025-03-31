import {EventEmitter} from "../../core/EventEmitter.js";

export class Selector
{
	#filter = "";
	#selectedMediaId = 0;
	#selectedMediaLink = "";
	#emitter = new EventEmitter();

	#dragItem = null;
	#isDragDrop = true;
	#dropTarget = null;


	#treeViewWrapper  = {};
	#mediaService = {};
	#selectorView = {};

	constructor(treeViewWrapper, mediaService, selectorView)
	{
		this.#treeViewWrapper = treeViewWrapper;
		this.#mediaService = mediaService;
		this.#selectorView = selectorView;
		this.#initEvents();
	}

	set filter(value)
	{
		this.#filter = value;
	}

	get selectedMediaId()
	{
		return this.#selectedMediaId;
	}

	get selectedMediaLink()
	{
		return this.#selectedMediaLink;
	}

	set dropTarget(value)
	{
		this.#dropTarget = value;
	}

	set isDragDrop(value)
	{
		this.#isDragDrop = value;
	}

	on(eventName, listener)
	{
		return this.#emitter.on(eventName, listener);
	}

	off(eventName, listener)
	{
		return this.#emitter.off(eventName, listener);
	}

	async showSelector(element)
	{
		element.innerHTML = await this.#mediaService.loadSelectorTemplate();

		this.#treeViewWrapper.initTree();
	}

	async loadMedia(nodeId)
	{
		return await this.#mediaService.loadFilteredMediaByNodeId(nodeId, this.#filter);
	}

	displayMediaList(mediaList)
	{
		this.#selectorView.displayMediaList(mediaList);

		if (this.#isDragDrop === true)
			this.#prepareDragDrop();
	}


	#initEvents()
	{
		this.#treeViewWrapper.on("loadMediaInDirectory", async (args) =>
		{
			const results = await this.loadMedia(args.node_id);
			this.displayMediaList(results);
		});
	}

	#prepareDragDrop()
	{
		this.#dropTarget = document.getElementById("thePlaylist");
		for (const mediaItem of this.#selectorView.mediaItems)
		{
			mediaItem.addEventListener("dragstart", (event) =>
			{
				this.#dragItem = event.target;
				event.dataTransfer.effectAllowed = 'move';
			});
		}
		this.#dropTarget.addEventListener('dragover', (event) => {
			event.preventDefault();
		});
		this.#dropTarget.addEventListener('drop', (event) => {
			event.preventDefault();
//			this.#dropTarget.appendChild(this.#dragItem);
			this.#emitter.emit('loadMediaInDirectory', { item: this.#dragItem });
			this.#dragItem = null;
		});
	}


}
