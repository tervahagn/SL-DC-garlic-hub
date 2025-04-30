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

export class PlayerActions
{
    #selectPlaylist = document.getElementsByClassName("select-playlist");
    #removePlaylist = document.getElementsByClassName("remove-playlist");
    #autocompleteFactory = null;
    #playerNameAutocomplete = null;

    #playerService = null;
    constructor(autoCompleteFactory, PlayerService)
    {
        this.#autocompleteFactory = autoCompleteFactory;
        this.#playerService = PlayerService;
    }

   init()
   {
        for (let i = 0; i < this.#selectPlaylist.length; i++) {
            const element = this.#selectPlaylist[i];
            element.addEventListener("click", async (event) => {
                let parentWithDataId = this.#findDataIdInResultsBody(event.target)
                let playerId = 0;
                if (parentWithDataId !== null)
                    playerId = parentWithDataId.dataset.id;
                else
                    return;

             let editPlaylist = parentWithDataId.querySelector(".playlist_id");

            this.#playerNameAutocomplete = this.#autocompleteFactory.create("playlist_name", "/async/playlists/find/for-player/");
            this.#playerNameAutocomplete.initWithCreateFields(editPlaylist);
            this.#playerNameAutocomplete.getHiddenIdElement().addEventListener("change", async (event) => {
                const playlist_id = event.target.value;
                const result = await this.#playerService.replacePlaylist(playerId, event.target.value);
                if (result.success)
                {
                    this.#playerNameAutocomplete.restore(result.playlist_name);
                    const li1 = document.createElement("li");
                    const a1 = document.createElement("a");
                    a1.href = "#";
                    a1.dataset.action   = "playlist";
                    a1.dataset.actionId = playlist_id;
                    a1.className = "bi bi-x-circle remove-playlist";
                    li1.appendChild(a1);
                    this.#addRemoveEventListener(a1);

                    const li2 = document.createElement("li");
                    const a2 = document.createElement("a");
                    a2.href = "/playlists/compose/" + playlist_id;
                    a2.dataset.action   = "playlist";
                    a2.dataset.actionId = playlist_id;
                    a2.className = "bi bi-music-note-list playlist-link";
                    li2.appendChild(a2);
                    const actions = parentWithDataId.querySelector(".actions ul");
                    actions.appendChild(li1);
                    actions.appendChild(li2);
                }
            });


            });
        }

       for (let i = 0; i < this.#removePlaylist.length; i++) {
           this.#addRemoveEventListener(this.#removePlaylist[i]);
        }
    }

    #addRemoveEventListener(element)
    {
        element.addEventListener("click", async (event) => {
            let parentWithDataId = this.#findDataIdInResultsBody(event.target)
            let playerId = 0;
            if (parentWithDataId !== null)
                playerId = parentWithDataId.dataset.id;
            else
                return;
            const result = await this.#playerService.replacePlaylist(playerId, 0);
            if (!result.success)
                return;

            parentWithDataId.querySelector(".playlist_id").innerText = result.playlist_name;
            parentWithDataId.querySelector(".playlist-link").parentElement.remove();
            event.target.parentElement.remove();
        });
    }

    #findDataIdInResultsBody(element)
    {
        let currentElement = element;

        while (currentElement)
        {
            if (currentElement.classList && currentElement.classList.contains('results-body'))
            {
                return currentElement;
            }
            currentElement = currentElement.parentElement;
        }

        return null;
    }

}
