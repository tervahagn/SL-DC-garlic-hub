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
    #menuElement = null;
    #fetchClient = null;
    #mediaDialog  = null;

    constructor(menuElement, fetchClient, mediaDialog)
    {
        this.#menuElement = menuElement;
        this.#fetchClient = fetchClient;
        this.#mediaDialog = mediaDialog;
    }

    show(event)
    {
        document.querySelectorAll('.context_menu').forEach(el => el.remove());  // remove all previous menu
        this.#menuElement.style.left = `${event.pageX}px`;
        this.#menuElement.style.top = `${event.pageY}px`;
        document.body.appendChild(this.#menuElement);
        document.addEventListener('click', () => this.#menuElement.remove(), {once: true});
    }

    addEditEvent(editMediaMenuElement, currentMediaId, lang)
    {
        editMediaMenuElement.addEventListener("click", () => {
     //       this.#mediaDialog.prepareShow("edit_media", lang);
            this.#mediaDialog.show();
        });
    }

    addRemoveEvent(removeMediaMenuElement, currentMedia)
    {
        removeMediaMenuElement.addEventListener("click", () => {
            (async () => {

                const apiUrl = "/async/mediapool/media";
                const dataToSend = {"media_id": currentMedia.getAttribute('data-media-id')};
                const options = {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(dataToSend)
                }

                const result = await this.#fetchClient.fetchData(apiUrl, options).catch(error => {
                    console.error('Fetch error:', error.message);
                    return null;
                });

                let result_obj = JSON.parse(result);
                if (!result_obj || !result_obj.success)
                {
                    console.error('Error:', result_obj?.error_message || 'Unknown error');
                    return;
                }

                currentMedia.remove();
            })();
        });
    }

}