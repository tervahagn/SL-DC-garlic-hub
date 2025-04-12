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
	#itemTemplate = null;

	constructor()
	{
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
		this.#getTemplateElement();

		for (const item of items)
		{
			const articleElement = document.createElement('article');

			playlistsListElement.appendChild( this.#createArticleElement(item));
		}
	}

	#createArticleElement(item)
	{
		const element = this.#itemTemplate.content.cloneNode(true);
		const el = element.querySelector(".playlist-select-item");
		el.setAttribute("data-select-id", item.id);
		const span = element.querySelector(".playlist-select-name");

		span.textContent = item.name;

		return el;
	}

	#getTemplateElement()
	{
		this.#itemTemplate =  document.getElementById("playlistSelectTemplate");
	}

}