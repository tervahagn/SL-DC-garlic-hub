import {Utils} from "../../../../core/Utils.js";

export class Item
{
	#itemData = null
	#playlistItem = null;
	#cmsEdition = document.getElementById("cms_edition").value;

	#linkPlaylistAction    = null;
	#editTemplateAction    = null;
	#conditionalPlayAction = null;
	#editTriggerAction     = null;
	#editWidgetAction      = null;
	#editSettingsAction    = null;
	#copyItemAction        = null;
	#deleteItemAction      = null;

	constructor(itemData)
	{
		this.#itemData = itemData;
	}


	get linkPlaylistAction()
	{
		return this.#linkPlaylistAction;
	}

	get editTemplateAction()
	{
		return this.#editTemplateAction;
	}

	get conditionalPlayAction()
	{
		return this.#conditionalPlayAction;
	}

	get editTriggerAction()
	{
		return this.#editTriggerAction;
	}

	get editWidgetAction()
	{
		return this.#editWidgetAction;
	}

	get editSettingsAction()
	{
		return this.#editSettingsAction;
	}

	get copyItemAction()
	{
		return this.#copyItemAction;
	}

	get deleteItemAction()
	{
		return this.#deleteItemAction;
	}

	buildItemElement()
	{
		const template = document.getElementById("playlistItemTemplate");
		const playlistItem = template.content.cloneNode(true);
		this.#playlistItem = playlistItem;

		const listItem = playlistItem.querySelector('.playlist-item');
		listItem.dataset.mediaId = this.#itemData.file_resource;
		listItem.id = "itemId-" + this.#itemData.item_id;

		const thumbnail = playlistItem.querySelector('img');
		thumbnail.src = "/" + this.#itemData.paths.thumbnail.replace('public/', '');
		thumbnail.alt = this.#itemData.item_name;

		const itemName = playlistItem.querySelector('.item-name');
		itemName.textContent = this.#itemData.item_name;

		const itemDuration = playlistItem.querySelector('.item-duration');

		itemDuration.textContent = Utils.formatSecondsToTime(this.#itemData.item_duration);

		playlistItem.querySelector('.actions').setAttribute('data-item-id', this.#itemData.item_id)
		this.#initActions();

		return playlistItem;
	}

	#initActions()
	{
		if (this.#itemData.item_type === "playlist")
			this.#linkPlaylistAction = this.#playlistItem.querySelector('.link-playlist');
		else
			this.#playlistItem.querySelector('.link-playlist').remove();

		if (this.#itemData.item_type === "template")
			this.#editTemplateAction = this.#playlistItem.querySelector('.edit-template');
		else
			this.#playlistItem.querySelector('.edit-template').remove();

		if (this.#cmsEdition !== "edge")
		{
			this.#conditionalPlayAction = this.#playlistItem.querySelector('.conditional-play');
			this.#editTriggerAction     = this.#playlistItem.querySelector('.edit-trigger');
			this.#editSettingsAction    = this.#playlistItem.querySelector('.edit-settings');
		}
		else
		{
			this.#playlistItem.querySelector('.conditional-play').remove();
			this.#playlistItem.querySelector('.edit-trigger').remove();
			this.#playlistItem.querySelector('.edit-settings').remove();
		}

		if (this.#itemData.mimetype === "application/widget")
			this.#editWidgetAction  = this.#playlistItem.querySelector('.edit-widget');
		else
			this.#playlistItem.querySelector('.edit-widget').remove();

		if (this.#itemData.item_type === "template" || this.#itemData.item_type === "playlist")
			this.#copyItemAction = this.#playlistItem.querySelector('.copy-item');
		else
			this.#playlistItem.querySelector('.copy-item').remove();

		this.#deleteItemAction = this.#playlistItem.querySelector('.delete-item');
	}


}