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

export class MediaList
{
    #mediaListElement = null;
    #mediaFactory = null
    #contextMenuFactory = null;

    constructor(mediaListElement, mediaFactory, contextMenuFactory)
    {
        this.#mediaListElement   = mediaListElement;
        this.#mediaFactory       = mediaFactory;
        this.#contextMenuFactory = contextMenuFactory;
    }

    render(data)
    {
        this.#mediaListElement.innerHTML = ""; // Clear previous content

        data.forEach((media) => {
            this.#addMediaToList(media);
        });

        const lightbox = GLightbox({
            plyr: {
                css: "/css/external/plyr.css",
                js: "/js/external/plyr.js"
            },
            width: "90vw",
            height: "90vh",
            loop: false,
            autoplayVideos: true

        });
    }

    deleteMediaDomBy(dataMediaId)
    {
        const element = document.querySelector(`[data-media-id="${dataMediaId}"]`);
        if (element)
            element.remove();
    }

    toggleUploader(show)
    {
        document.getElementById("file-uploader").style.display = show ? "block" : "none";
    }

    #addMediaToList(media)
    {
        const mediaObject = this.#mediaFactory.create();
        const mediaItem   = mediaObject.buildMediaItem(media);

        this.#addContextMenu(mediaItem);

        this.#mediaListElement.appendChild(mediaItem);
    }

    #addContextMenu(mediaItem)
    {
        mediaItem.addEventListener("contextmenu", (event) => {
            event.preventDefault();

            const contextMenu    = this.#contextMenuFactory.create();
            contextMenu.show(event);

            contextMenu.addRemoveEvent(document.getElementById("remove_media"), mediaItem);
            contextMenu.addEditEvent(document.getElementById("edit_media"), mediaItem);
            contextMenu.addCloneEvent(document.getElementById("clone_media"), mediaItem, this.#addMediaToList.bind(this));
        });
    }
}
