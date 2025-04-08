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
		if (this.#isDragDrop === true)
			this.#prepareDrop();

	}

	async loadMedia(nodeId)
	{
		return await this.#mediaService.loadFilteredMediaByNodeId(nodeId, this.#filter);
	}

	displayMediaList(mediaList)
	{
		this.#selectorView.displayMediaList(mediaList);
		if (this.#isDragDrop === true)
			this.#prepareDrag();
	}


	#initEvents()
	{
		this.#treeViewWrapper.on("treeview:loadMediaInDirectory", async (args) =>
		{
			const results = await this.loadMedia(args.node_id);
			this.displayMediaList(results);
		});
	}

	#prepareDrag()
	{
		for (const media of this.#selectorView.mediaItems)
		{
			media.mediaItem.addEventListener("dragstart", (event) =>
			{
				this.#dragItem = media;
				event.dataTransfer.effectAllowed = 'copy';
			});
		}
	}

	#prepareDrop()
	{
		this.#dropTarget.addEventListener('dragover', (event) => {
			event.preventDefault();
		});
		this.#dropTarget.addEventListener('drop', (event) => {

			event.preventDefault();
			this.#emitter.emit('mediapool:selector:drop', {media: this.#dragItem });
			this.#dragItem = null;
		});
	}
}
