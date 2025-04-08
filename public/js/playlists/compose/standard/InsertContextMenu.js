export class InsertContextMenu
{
	#insertMedia             = document.getElementById("insertMedia");
	#insertExternalMedia     = document.getElementById("insertExternalMedia");
	#insertPlaylists         = document.getElementById("insertPlaylists");
	#insertExternalPlaylists = document.getElementById("insertExternalPlaylists");
	#insertTemplates         = document.getElementById("insertTemplates");
	#insertChannels          = document.getElementById("insertChannels");
	#insertMenu              = document.getElementById("insertMenu");
	#selectorFactory         = null;
	#itemSelectContainer     = document.getElementById("itemSelectContainer");
	#itemService             = null;
	#itemList                = null;

	constructor(selectorFactory, itemList, itemService)
	{
		this.#selectorFactory = selectorFactory;
		this.#itemService     = itemService;
		this.#itemList        = itemList;
	}

	init(playlistId)
	{
		this.#insertMedia.addEventListener("click", async () =>
		{
			const selector = this.#selectorFactory.create("mediaselector");
			selector.showSelector(this.#itemSelectContainer);
			selector.on("mediapool:selector:drop", async (args) =>
			{
				let result = await this.#itemService.insertFromMediaPool(args.media.mediaId, playlistId, args.position);
				this.#itemList.createPlaylistItem(result.data.item, args.position);
				this.#itemList.displayPlaylistProperties(result.data.playlist);
			});

			//	this.#insertMenu.querySelector(".context-menu").style.display = "none";
		});

		this.#insertExternalMedia.addEventListener("click", () =>
		{
			alert("Insert external media");
		});

		this.#insertPlaylists.addEventListener("click", () =>
		{
			alert("Insert playlists");
		});

		this.#insertExternalPlaylists.addEventListener("click", () =>
		{
			alert("Insert external playlists");
		});
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
