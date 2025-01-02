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

import { PreviewFactory } from "./Preview/PreviewFactory.js";

export class FilePreviews
{
    dropzonePreview = null;
    startFileUpload = null;
    previewFactory  = new PreviewFactory();
    fileList        = {};

    constructor(dropzonePreview, startFileUpload, previewFactory)
    {
        this.dropzonePreview = dropzonePreview;
        this.previewFactory  = previewFactory;
        this.startFileUpload = startFileUpload;
        this.startFileUpload.disabled = true;
    }

    getFileList()
    {
        return this.fileList;
    }

    handleFiles(files)
    {
        Array.from(files).forEach(file => {
            try
            {
                const previewHandler = this.previewFactory.create(file);
                const metadata       = previewHandler.extractMetadata(file);
                const previewElement = previewHandler.createPreview();
                previewElement.className = "previewElement";

                const id = crypto.randomUUID();
                this.fileList[id] = file;
                const previewContainer = this.createPreviewContainer(metadata, previewElement, id);
                this.dropzonePreview.appendChild(previewContainer);
                this.startFileUpload.disabled = false;
            }
            catch (error)
            {
                alert(`${file.name} not supported.` + error);
            }
        });
    }

    createPreviewContainer(metadata, previewElement, id)
    {
        const container     = document.createElement("div");
        container.className = "previewContainer";

        // Tooltip
        const tooltip     = document.createElement("div");
        tooltip.className = "tooltip";
        tooltip.innerHTML = `${metadata.name}<br> (${metadata.size})`;
        container.appendChild(tooltip);
        container.addEventListener("mouseenter", () => {tooltip.style.visibility = "visible";});
        container.addEventListener("mouseleave", () => {tooltip.style.visibility = "hidden";});

        // Close button
        const closeButton = document.createElement("button");
        closeButton.className = "closeButton";
        closeButton.textContent = "×";

        container.setAttribute('data-preview-id', id);
        closeButton.addEventListener("click", () => {
            this.removeFromPreview(id);
        });

        container.appendChild(closeButton);
        container.appendChild(previewElement);
        return container;
    }

    removeFromPreview(id)
    {
        delete this.fileList[id];
        document.querySelector(`[data-preview-id="${id}"]`).remove();

        if (Object.keys(this.fileList).length === 0)
            this.startFileUpload.disabled = true;
    }
}