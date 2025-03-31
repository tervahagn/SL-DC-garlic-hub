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

import {DirectoryView} from "../treeview/DirectoryView.js";

export class Media
{
    #mediaElement = null;

    constructor(mediaElement)
    {
        this.#mediaElement = mediaElement;
    }

    buildMediaItem(mediaData)
    {
        this.#mediaElement.querySelector(".media-type-icon").classList.add(this.#detectMediaType(mediaData.mimetype));
        this.#mediaElement.querySelector(".media-item").setAttribute("data-media-id", mediaData.media_id);
        const img = this.#mediaElement.querySelector("img");
        img.src = "/var/mediapool/thumbs/"+mediaData.checksum+"." + mediaData.thumb_extension;
        img.alt = "Thumbnail: " + mediaData.filename;
		img.setAttribute("data-title", mediaData.filename);

		const a = this.#mediaElement.querySelector("a");

/*        const durationElement = this.#mediaElement.querySelector(".media-duration");
        if (mediaData.metadata.duration !== undefined && mediaData.metadata.duration > 0 )
        {
            durationElement.textContent = this.#formatSeconds(mediaData.metadata.duration);
            durationElement.parentElement.style.display = "block";
        }
*/
        const mediaItem = this.#mediaElement.querySelector(".media-item");

		return mediaItem;
    }

    #detectMediaType(mimetype)
    {
        const first = mimetype.split("/")[0]

        if (first === "audio")
            return "bi-music-note";
        else if (first === "video")
            return "bi-film";
        else if (first === "image")
            return "bi-image";

        switch(mimetype.split("/")[1])
        {
            case "pdf":
                return "bi-filetype-pdf";
            case "zip":
            case "widget":
            case "wgt":
                return "bi-file-zip";

        }

        return "bi-file-earmark";
    }

    #formatSeconds(seconds)
    {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;

        return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(secs).padStart(2, "0")}`;
    }

}