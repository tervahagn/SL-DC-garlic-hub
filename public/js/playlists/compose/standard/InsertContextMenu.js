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

	constructor(selectorFactory)
	{
		this.#selectorFactory = selectorFactory;
	}

	init()
	{
		this.#insertMedia.addEventListener("click", () =>
		{
			const selector = this.#selectorFactory.create("mediaselector");
			selector.showSelector(this.#itemSelectContainer);
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
