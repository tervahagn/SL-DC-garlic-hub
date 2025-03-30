export class MediaSelector
{
	#dom_tree = "";
	#dom_media = "";
	#radio_filter = "";
	#selected_media_id = 0;
	#selected_media_link = "";
	#selected_media_ext = "";
	#selected_media_name = "";
	#treeViewWrapper  = {};
	#mediaService = {};
	#selectorView = {};

	constructor(treeViewWrapper, mediaService, selectorView)
	{
		this.#treeViewWrapper = treeViewWrapper;
		this.#mediaService    = mediaService;
		this.#selectorView    = selectorView;
	}

	setMediaFilter(filter)
	{
		this.#radio_filter = filter;
	}

	setDomContainer(tree_container, content_container)
	{
		this.#dom_tree = tree_container;
		this.#dom_media = content_container;
	}

	getSelectedMediaId()
	{
		return this.#selected_media_id;
	}

	getSelectedMediaLink()
	{
		return this.#selected_media_link;
	}

	async showSelector(element)
	{
		element.innerHTML = await this.#mediaService.loadSelectorTemplate();

		this.#treeViewWrapper.initTree();
	}

	async loadMedia(nodeId, filter)
	{
		return await this.#mediaService.loadFilteredMediaByNodeId(nodeId, filter);
	}
	
	getSelectedMedia()
	{
		return {
			media_id: this.#selected_media_id,
			media_link: this.#selected_media_link,
			media_name: this.#selected_media_name,
			media_type: this.#selected_media_ext
		};
	}

	fillMediaArea(mediaObj)
	{
		this.#selectorView.listMedia();
	}
}
