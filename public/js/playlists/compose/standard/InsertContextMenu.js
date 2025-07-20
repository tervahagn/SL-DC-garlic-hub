export class InsertContextMenu
{
	#insertMedia             = document.getElementById("insertMedia");
	#insertExternalMedia     = document.getElementById("insertExternalMedia");
	#insertPlaylists         = document.getElementById("insertPlaylists");
	#insertExternalPlaylists = document.getElementById("insertExternalPlaylists");
	#insertTemplates         = document.getElementById("insertTemplates");
	#insertChannels          = document.getElementById("insertChannels");
	// unused? #insertMenu              = document.getElementById("insertMenu");
	#selectorFactory         = null;
	#dragDropHandler         = null;
	#itemSelectContainer     = document.getElementById("itemSelectContainer");

	constructor(selectorFactory, dragDropHandler)
	{
		this.#selectorFactory = selectorFactory;
		this.#dragDropHandler = dragDropHandler;
	}

	init()
	{
		this.#insertMedia.addEventListener("click", async () =>
		{
			const selector = this.#selectorFactory.create("mediapool");
			await selector.showSelector(this.#itemSelectContainer);
			this.#dragDropHandler.source = "mediapool";
			this.#dragDropHandler.items = selector.getMediaItems();
			const container = selector.getMediaItemsContainer();
			this.#dragDropHandler.addDropSource(container);
			//	this.#insertMenu.querySelector(".context-menu").style.display = "none";
		});

		if (this.#insertExternalMedia !== null)
		{
			this.#insertExternalMedia.addEventListener("click", () =>
			{
				alert("Insert external media");
			});
		}

		if (this.#insertPlaylists !== null)
		{
			this.#insertPlaylists.addEventListener("click", async () =>
			{
				const selector = this.#selectorFactory.create("playlists");
				await selector.showSelector(this.#itemSelectContainer);
				this.#dragDropHandler.source = "playlists";
				this.#dragDropHandler.items = selector.items;
				const container = selector.getItemsContainer();
				this.#dragDropHandler.addDropSource(container);
				//	this.#insertMenu.querySelector(".context-menu").style.display = "none";
			});
		}
		if (this.#insertExternalPlaylists !== null)
		{
			this.#insertExternalPlaylists.addEventListener("click", () =>
			{
				alert("Insert external playlists");
			});
		}
		if (this.#insertTemplates !== null)
		{
			this.#insertTemplates.addEventListener("click", () =>
			{
				alert("insert Templates");
			});
		}
		if (this.#insertChannels !== null)
		{
			this.#insertChannels.addEventListener("click", () =>
			{
				alert("Insert channels");
			});
		}
	}
}
