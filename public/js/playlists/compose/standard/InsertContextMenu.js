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
			const selector = this.#selectorFactory.create("mediaselector");
			await selector.showSelector(this.#itemSelectContainer);
			this.#dragDropHandler.mediaItems = selector.getMediaItems();
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

		this.#insertPlaylists.addEventListener("click", () =>
		{
			alert("Insert playlists");
		});

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
