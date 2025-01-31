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

import {MediaApiConfig} from "./MediaApiConfig.js";

export class MediaDialog
{
    #dialogElement = null;
    #closeElement = null;
    #action       = "";
    #fetchClient  = null;

    constructor(dialog_element, close_element, fetchClient)
    {
        this.#dialogElement = dialog_element;
        this.#closeElement  = close_element;
        this.#fetchClient   = fetchClient;

        this.#addCancelEvent();
        this.#addSaveEvent();
    }

    prepareShow(action, lang)
    {
        this.#action = action;
        this.#dialogElement.querySelector("legend").textContent = lang[action];
    }

    show()
    {

        this.#dialogElement.showModal();
    }

    #addSaveEvent()
    {
        // also for closing the dialog with the cancel button
        this.#dialogElement.addEventListener('close', () => {
            if (this.#dialogElement.returnValue !== "submit")
                return;

            (async () => {
                const method     = "POST";
                const apiUrl     = MediaApiConfig.BASE_URI + "/";
                const dataToSend = ""; //this.#determineDataToSend();
                const options    = {method: method, headers: {'Content-Type': 'application/json'}, body: JSON.stringify(dataToSend)}

                const result = await this.#fetchClient.fetchData(apiUrl, options).catch(error => {
                    console.error('Fetch error:', error.message);
                    return null;
                });

                if (!result || !result.success)
                {
                    console.error('Error:', result?.error_message || 'Unknown error');
                    return;
                }

                // change DOM
            })();
        });
    }

    #addCancelEvent()
    {
        this.#closeElement.addEventListener("click", () => {
            this.#dialogElement.close("cancel");
        });
    }
}