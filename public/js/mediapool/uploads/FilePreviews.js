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
    previewFactory  = new PreviewFactory();
    fileList        = [];

    constructor(dropzonePreview, previewFactory)
    {
        this.dropzonePreview = dropzonePreview;
        this.previewFactory = previewFactory;
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
                const previewElement = previewHandler.createPreview(file);
                previewElement.className = "previewElement";

                const previewContainer = this.createPreviewContainer(metadata, previewElement, file);
                this.dropzonePreview.appendChild(previewContainer);
                this.fileList.push(file);
            }
            catch (error)
            {
                alert(`${file.name} not supported.` + error);
            }
        });
    }

    createPreviewContainer(metadata, previewElement, file)
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
        closeButton.addEventListener("click", () => {
            const index = this.fileList.indexOf(file);
            if (index > -1) {
                this.fileList.splice(index, 1);
            }
            container.remove();
        });

        container.appendChild(closeButton);
        container.appendChild(previewElement);
        return container;
    }
}