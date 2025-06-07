/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

import {Utils} from "../../../../core/Utils.js";
import {WidgetsService} from "../editors/WidgetsService.js";
import {FetchClient} from "../../../../core/FetchClient.js";
import {CreateWidgetForm} from "../editors/CreateWidgetForm.js";
import {EditDialog} from "../editors/EditDialog.js";

export class Item
{
	#itemData = null
	#itemsService = null;
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
	#itemName     = null;
	#editItemName = false;
	#itemDuration = null;
	#isItemDurationInProcess = false;

	constructor(itemData, itemsService)
	{
		this.#itemData = itemData;
		this.#itemsService = itemsService;
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

		const listItem = playlistItem.querySelector(".playlist-item");
		listItem.dataset.mediaId = this.#itemData.file_resource;
		listItem.id = "itemId-" + this.#itemData.item_id;

		const thumbnail = playlistItem.querySelector('img');
		thumbnail.src = "/" + this.#itemData.paths.thumbnail.replace('public/', '');
		thumbnail.alt = this.#itemData.item_name;

		this.#itemName = playlistItem.querySelector('.item-name');
		this.#buildItemName()

		this.#itemDuration = playlistItem.querySelector('.item-duration');
		this.#buildItemDuration();

		playlistItem.querySelector('.actions').setAttribute('data-item-id', this.#itemData.item_id)
		this.#initActions();

		return playlistItem;
	}

	#buildItemName()
	{
		this.#itemName.textContent = this.#itemData.item_name;
		if (this.#itemData.item_type === "mediapool")
		{
			this.#itemName.contentEditable = true;
			this.#itemName.addEventListener('keydown', (event) => {
				if (event.key === 'Enter')
				{
					event.preventDefault(); // prevent a carriage return.
					this.#itemsService.editItem(this.#itemData.item_id, 'item_name', this.#itemName.textContent)
					this.#editItemName = false;
					this.#itemName.blur();
				}
			});
			this.#itemName.addEventListener('focus', () => {
				this.#editItemName = true;
			});
			this.#itemName.addEventListener('blur', () => {
				if (this.#editItemName)
				{
					this.#itemsService.editItem(this.#itemData.item_id, 'item_name', this.#itemName.textContent)
					this.#editItemName = false;
				}
			});
		}
	}

	#buildItemDuration()
	{
		this.#itemDuration.textContent = Utils.formatSecondsToTime(this.#itemData.item_duration);
		this.#itemDuration.dataset.seconds = this.#itemData.item_duration;
		this.#itemDuration.addEventListener('click', (event) => {
			const target = event.target;
			if (target.tagName === "SPAN")
			{
				const inputGroup = document.createElement("span");
				inputGroup.classList.add("item-duration-input");
				const inputField = document.createElement("input");
				inputField.type = 'number';
				inputField.min = 0;
				inputField.step = 1;
				inputField.value = target.dataset.seconds;
				inputGroup.appendChild(inputField);

				const resetButton = document.createElement("i");
				resetButton.classList.add("duration-reset");
				resetButton.classList.add("bi");
				resetButton.classList.add("bi-arrow-clockwise");
				inputGroup.appendChild(resetButton);

				resetButton.addEventListener('click', async () => {
					this.#isItemDurationInProcess = true;
					const result = await this.#itemsService.fetchDefaultSeconds(this.#itemData.item_id);
					if (!result.success)
						return;

					inputField.value = result.item.default_duration;
					await this.#saveDuration(inputField);
					resetButton.remove();
				});

				inputField.addEventListener('blur', () => {
					if (this.#isItemDurationInProcess)
					{
						this.#isItemDurationInProcess = false;
						return;
					}

					this.#saveDuration(inputField);
					resetButton.remove();
				});

				inputField.addEventListener('keydown', (event) => {
					if (event.key === 'Enter')
					{
						this.#isItemDurationInProcess = true;
						this.#saveDuration(inputField);
						resetButton.remove();
					}
				});

				target.parentNode.replaceChild(inputGroup, target);
				inputField.focus();
			}
		});
	}

	async #saveDuration(inputElement)
	{
		const result = await this.#itemsService.editItem(this.#itemData.item_id, 'item_duration', inputElement.value);
		if (!result.success)
			return;

		this.#itemDuration.dataset.seconds = inputElement.value;
		this.#itemDuration.textContent = Utils.formatSecondsToTime(inputElement.value);

		inputElement.parentNode.replaceChild(this.#itemDuration, inputElement);
		inputElement.remove();
	}


	#initActions()
	{
		if (this.#itemData.item_type === "playlist")
		{
			this.#linkPlaylistAction = this.#playlistItem.querySelector('.link-playlist');
			this.#linkPlaylistAction.href = "/playlists/compose/" + this.#itemData.file_resource;
		}
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
		{
			this.#editWidgetAction  = this.#playlistItem.querySelector('.edit-widget');
			this.#editWidgetAction.addEventListener("click", async () =>
			{
				let widgetsService = new WidgetsService(new FetchClient());
				let widgetData =  await widgetsService.fetchConfiguration(this.#itemData.item_id);
				let widgetForm = new CreateWidgetForm();
				let editDialog =  new EditDialog()
				editDialog.setTitle(widgetData.data.item_name);
				editDialog.setId(widgetData.data.item_id);
				widgetForm.parsePreferences(widgetData.data.preferences, widgetData.data.values);
				editDialog.setContent(widgetForm.getForm());

				let saveCallBack = async function (e)
				{
					e.preventDefault();
					let values = widgetForm.collectValues();
					let result = await widgetsService.saveWidgetValues(widgetData.data.item_id, values);
					if (result.success === false)
					{
						editDialog.setErrorText(jsonResponse.message);
					}
					else
					{
						editDialog.closeDialog();
						// jPlaylist.setSaveAlert();
					}


				}
				editDialog.onSave(saveCallBack);
				editDialog.onCancel();

				editDialog.openDialog();
			});
		}
		else
			this.#playlistItem.querySelector('.edit-widget').remove();

		if (this.#cmsEdition !== "edge" && (this.#itemData.item_type === "template" || this.#itemData.item_type === "playlist"))
			this.#copyItemAction = this.#playlistItem.querySelector('.copy-item');
		else
			this.#playlistItem.querySelector('.copy-item').remove();

		this.#deleteItemAction = this.#playlistItem.querySelector('.delete-item');
	}


}