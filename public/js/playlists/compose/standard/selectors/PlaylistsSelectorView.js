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

export class PlaylistsSelectorView
{
	#items = {};

	constructor()
	{
	}

	get items()
	{
		return this.#items;
	}

	getPlaylistsListElement()
	{
		// because cache can prevent to execute
		return document.getElementById("playlistsList");
	}

	displayList(items)
	{
		const playlistsListElement = document.getElementById("playlistsList");
		playlistsListElement.innerHTML = "";

		for (const item of items)
		{
			const articleElement = document.createElement('article');

			playlistsListElement.appendChild(this.#createArticleElement(item));
		}
	}

	#createArticleElement(item)
	{
		const articleElement = document.createElement('article');
		articleElement.className = 'playlist-item';
		articleElement.dataset.id = item.id;
		articleElement.textContent = item.name;
		return articleElement;
	}

}