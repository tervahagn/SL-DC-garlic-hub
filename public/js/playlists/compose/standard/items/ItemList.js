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

export default class ItemList
{
	#itemFactory = null;
	#dropTarget = null;
	#itemsService = null;
	#itemsList   = {};
	#playlistId = 0;

	constructor(itemFactory, itemsService, dropTarget)
	{
		this.#itemFactory  = itemFactory;
		this.#dropTarget   = dropTarget;
		this.#itemsService = itemsService;
	}

	async displayPlaylist(playlistId, playlistProperties)
	{
		this.#playlistId = playlistId;
		const results = await this.#itemsService.loadByPlaylistId(playlistId);
		if (!results.success)
			return;

		for (const item of results.data.items)
		{
			this.createPlaylistItem(item);
		}

		playlistProperties.display(results.data.playlist);
	}

	createPlaylistItem(itemData, position = null)
	{
		const item = this.#itemFactory.create(itemData);
		this.#itemsList[itemData.item_id] = item;

		// console.log('Listenlänge', this.#dropTarget.children.length, 'Position', position);

		if (position === null || this.#dropTarget.children.length < position)
			this.#dropTarget.appendChild(item.buildItemElement());
		else
		{
			const prevItem = this.#dropTarget.children[position - 1];
			this.#dropTarget.insertBefore(item.buildItemElement(), prevItem);
		}

		this.#createActions(item);
	}

	#createActions(item)
	{
		if (item.deleteItemAction !== null)
		{
			item.deleteItemAction.addEventListener('click', async () => {
				const id = item.deleteItemAction.parentElement.getAttribute('data-item-id');
				const results =  await this.#itemsService.delete(this.#playlistId, id);
				if (!results.success)
					return;

				document.getElementById("itemId-" + id).remove();
			});
		}
	}

}