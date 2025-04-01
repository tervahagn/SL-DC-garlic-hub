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
	#mediaId       = ""
	#thumbnailPath = "";
	#originalPath  = "";
	#fileName      = "";
	#mimetype      = "";
	#description   = ""
	#filesize      = 0;
	#username      = "";
	#dimensions    = "";
	#duration      = 0;

	constructor(mediaElement, mediaData)
    {
        this.#mediaElement = mediaElement;
		this.#mediaId       = mediaData.media_id;
		this.#thumbnailPath = "/var/mediapool/thumbs/"+mediaData.checksum+"." + mediaData.thumb_extension;
		this.#originalPath  = "/var/mediapool/originals/"+mediaData.checksum+"." + mediaData.extension;
		this.#fileName      = mediaData.filename;
		this.#mimetype		= mediaData.mimetype;
		this.#description   = mediaData.media_description
		this.#username      = mediaData.username;
		this.#filesize     	= mediaData.metadata.size
		if (mediaData.metadata.dimensions !== undefined && Object.keys(mediaData.metadata.dimensions).length > 0 )
			this.#dimensions = mediaData.metadata.dimensions.width + "x" + mediaData.metadata.dimensions.height;
		if (mediaData.metadata.duration !== undefined && mediaData.metadata.duration > 0)
			this.#duration = mediaData.metadata.duration;
    }

	buildElementForDisplayInMediaPool()
    {
		this.#createThumbnail()

		const a = this.#mediaElement.querySelector("a");
		if (this.#hasDetailedView(this.#mimetype))
			a.href  = this.#originalPath;
		else
		{
			a.classList.remove("glightbox");
			a.style.display = "none";
		}
        this.#mediaElement.querySelector(".media-owner").textContent    = this.#username;
        this.#mediaElement.querySelector(".media-filename").textContent = this.#fileName;
        this.#mediaElement.querySelector(".media-filesize").textContent = this.#formatBytes(this.#filesize);
        this.#mediaElement.querySelector(".media-mimetype").textContent = this.#mimetype;

        const dimensionsElement = this.#mediaElement.querySelector(".media-dimensions");
        if (this.#dimensions !== "")
        {
            dimensionsElement.textContent = this.#dimensions;
            dimensionsElement.parentElement.style.display = "block";
        }

        const durationElement = this.#mediaElement.querySelector(".media-duration");
        if (this.#duration > 0 )
        {
            durationElement.textContent = this.#formatSeconds(this.#duration);
            durationElement.parentElement.style.display = "block";
        }

        const mediaItem = this.#mediaElement.querySelector(".media-item");
        mediaItem.addEventListener("dragstart", (event) => {
			DirectoryView.workaroundShitForMediaIdBecauseOfChrome = mediaItem.getAttribute("data-media-id");
        });

        return mediaItem;
    }

	buildMediaItem()
	{
		this.#createThumbnail();

		return this.#mediaElement.querySelector(".media-item");
	}

	#createThumbnail()
	{
		this.#mediaElement.querySelector(".media-type-icon").classList.add(this.#detectMediaType(this.#mimetype));
		this.#mediaElement.querySelector(".media-item").setAttribute("data-media-id", this.#mediaId);
		const img = this.#mediaElement.querySelector("img");
		img.src = this.#thumbnailPath;
		img.alt = "Thumbnail: " + this.#fileName;
		img.setAttribute("data-title", this.#fileName);
	}

	#hasDetailedView(mimetype)
	{
		const first = mimetype.split("/")[0]
		if (first === "audio" || first === "video" || first === "image")
			return true;

		return mimetype.split("/")[1] === "pdf";
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

    #formatBytes(bytes)
    {
        if (bytes >= 1073741824)  // 1 GB
            return (bytes / 1073741824).toFixed(2) + " GB";
        else if (bytes >= 1048576)  // 1 MB
            return (bytes / 1048576).toFixed(2) + " MB";
        else if (bytes >= 1024)  // 1 KB
            return (bytes / 1024).toFixed(2) + " KB";
        else
            return bytes + " Bytes"; // less 1 KB
    }

    #formatSeconds(seconds)
    {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;

        return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(secs).padStart(2, "0")}`;
    }

}