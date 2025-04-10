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

export class PlaylistsSelector
{
	#playlistSelectorView = null;
	#playlistsService = null;
	#items = null;

	constructor(playlistsService, playlistSelectorView)
	{
		this.#playlistSelectorView = playlistSelectorView;
		this.#playlistsService = playlistsService;
	}

	getItemsContainer()
	{
		return  document.getElementById("playlistsList");
	}

	get items()
	{
		return this.#items;
	}

	async showSelector(element)
	{
		element.innerHTML = await this.#playlistsService.loadSelectorTemplate();

		this.#items = await this.#playlistsService.findPlaylists('internal', '');
		this.#playlistSelectorView.displayList(this.#items);
	}
}