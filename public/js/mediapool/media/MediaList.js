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
    #templateElement = null;
    constructor(mediaListElement, templateElement)
    {
        this.#templateElement = templateElement;

        this.#mediaListElement = mediaListElement;
    }

    render(data)
    {
        this.#mediaListElement.innerHTML = ""; // Clear previous content

        data.forEach((media) => {

            this.#addMediaToList(media.filename, media.mimetype, media.checksum);
        });
    }

    toggleUploader(show)
    {
        document.getElementById("file-uploader").style.display = show ? "block" : "none";
    }

    #addMediaToList(filename, mimetype, checksum)
    {
        const imageUrl = "/var/mediapool/thumbs/"+checksum+".jpg";
        const mediaItem = this.#createMediaItem(filename, mimetype, imageUrl);
        this.#mediaListElement.appendChild(mediaItem);
    }

    #createMediaItem(filename, mimetype, imageUrl)
    {

        const clone = this.#templateElement.content.cloneNode(true);

        const mediaItem = clone.querySelector('.media-item');
        const img = clone.querySelector('img');
        const filenameElement = clone.querySelector('.media-filename');
        const mimetypeElement = clone.querySelector('.media-mimetype');

        img.src = imageUrl;
        img.alt = `Thumbnail for ${filename}`;
        filenameElement.textContent = filename;
        mimetypeElement.textContent = mimetype;

        return mediaItem;
    }
}
