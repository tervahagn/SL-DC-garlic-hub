/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
export class PlayerSettingsContextMenu
{
	#menu = {};

	constructor(contextMenuTemplate)
	{
		this.#menu = contextMenuTemplate.content.cloneNode(true).firstElementChild;
	}

	init(openActions)
	{
		const elementsArray = Array.from(openActions); // Konvertiert die HTMLCollection in ein Array

		for (let i = 0; i < openActions.length; i++)
		{
			openActions[i].addEventListener('click', (event) => {
				event.preventDefault();
				this.#menu.style.left = `${event.pageX}px`;
				this.#menu.style.top = `${event.pageY}px`;
				document.body.appendChild(this.#menu);
				event.stopPropagation(); // not to close menu immediately

				const currentId = event.target.dataset.id;
				const links = this.#menu.querySelectorAll('a');
				links.forEach(link =>
				{
					link.href = link.href + currentId;
				});

				document.addEventListener('click', () => this.#menu.remove(), { once: true });});
		}

	}
}