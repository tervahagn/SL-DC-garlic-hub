import {EventEmitter} from "../../core/EventEmitter.js";

export class Selector
{
	#filter = "";
	#selectedMediaId = 0;
	#selectedMediaLink = "";
	#emitter = new EventEmitter();

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

	getMediaItemsContainer()
	{
		return this.#selectorView.getMediaListElement();
	}

	getMediaItems()
	{
		return this.#selectorView.mediaItems;
	}

	get selectedMediaId()
	{
		return this.#selectedMediaId;
	}

	get selectedMediaLink()
	{
		return this.#selectedMediaLink;
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
	}

	#initEvents()
	{
		this.#treeViewWrapper.on("treeview:loadMediaInDirectory", async (args) =>
		{
			const results = await this.loadMedia(args.node_id);
			this.displayMediaList(results);
			this.#emitter.emit('mediapool:selector:loaded', {nodeId: args.node_id });
		});
	}
}
