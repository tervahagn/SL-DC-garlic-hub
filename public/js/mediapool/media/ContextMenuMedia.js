/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

export class ContextMenuMedia
{
    #menuElement  = null;
    #mediaService = null;
    #mediaDialog  = null;
	#currentMedia = null;

    constructor(menuElement, mediaService, mediaDialog, currentMedia)
    {
        this.#menuElement  = menuElement;
        this.#mediaService = mediaService;
        this.#mediaDialog  = mediaDialog;
		this.#currentMedia = currentMedia;
    }

    show(event)
    {

        document.querySelectorAll('.context_menu').forEach(el => el.remove());  // remove all previous menu
        this.#menuElement.style.left = `${event.pageX}px`;
        this.#menuElement.style.top = `${event.pageY}px`;
        document.body.appendChild(this.#menuElement);
        document.addEventListener('click', () => this.#menuElement.remove(), {once: true});
    }

	addInfoEvent(infoMediaMenuElement)
	{
		infoMediaMenuElement.addEventListener("click", () => {
			//this.#mediaInfoDialog.show(this.#mediaService);
		});
	}


	addEditEvent(editMediaMenuElement)
    {
        editMediaMenuElement.addEventListener("click", () => {
            this.#mediaDialog.show(this.#currentMedia);
        });
    }

    addCloneEvent(cloneMediaMenuElement, callback)
    {
        cloneMediaMenuElement.addEventListener("click", () => {
            (async () => {
                const newMedia = await this.#mediaService.cloneMedia(this.#currentMedia.getAttribute('data-media-id'));
                callback(newMedia);
            })();
        });
    }

    addRemoveEvent(removeMediaMenuElement)
    {
        removeMediaMenuElement.addEventListener("click", () => {
            (async () => {
                await this.#mediaService.removeMedia(this.#currentMedia.getAttribute('data-media-id'));
                this.#currentMedia.remove();
            })();
        });
    }

}