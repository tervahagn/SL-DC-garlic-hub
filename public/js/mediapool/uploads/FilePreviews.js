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
class FilePreviews
{
    dropzonePreview = null;
    fileList         = null;

    constructor(dropzonePreview, fileList = [])
    {
        this.dropzonePreview = dropzonePreview;
        this.fileList = fileList;
    }

    handleFiles(files)
    {
        Array.from(files).forEach(file => {
            if (file.type.startsWith("image/") || file.type.startsWith("video/") || file.type.startsWith("application/pdf"))
            {
                this.fileList.push(file);
                this.previewFile(file);
            }
            else
            {
                alert(`${file.name} wird nicht unterstützt.`);
            }
        });
    }

    previewFile(file)
    {
        const previewContainer = document.createElement("div");
        previewContainer.className = "previewContainer";
        const previewElement   = this.#determinePreviewElement(file.type);
        previewElement.className = "previewElement";

        // Schließen-Button hinzufügen
        const closeButton = document.createElement("button");
        closeButton.className = "closeButton";
        closeButton.textContent = "×";


        closeButton.addEventListener("click", () => {
            const index = this.fileList.indexOf(file);
            if (index > -1) {
                this.fileList.splice(index, 1);
            }
            previewContainer.remove();
        });

        previewContainer.appendChild(closeButton);

        // Tooltip hinzufügen
        const tooltip = document.createElement("div");
        tooltip.className = "tooltip";
        tooltip.innerHTML = `${file.name}<br> (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
        previewContainer.addEventListener("mouseenter", () => {tooltip.style.visibility = "visible";});
        previewContainer.addEventListener("mouseleave", () => {tooltip.style.visibility = "hidden";});
        previewContainer.appendChild(tooltip);

        if (file.type.startsWith("image/") || file.type.startsWith("video/"))
        {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewElement.src = e.target.result;
                if (file.type.startsWith("video/"))
                    previewElement.controls = true;
            };
            reader.readAsDataURL(file);
        }
        else if (file.type.startsWith("application/pdf"))
        {
            this.renderPDF(file, previewElement);
            previewContainer.appendChild(previewElement);
        }

        previewContainer.appendChild(previewElement);

        this.dropzonePreview.appendChild(previewContainer);
    }

    renderPDF(file, canvas)
    {
        const reader = new FileReader();
        reader.onload = function (e)
        {
            const pdfData = new Uint8Array(e.target.result);

            pdfjsLib.getDocument(pdfData).promise.then((pdf) => {
                pdf.getPage(1).then((page) => {
                    const viewport = page.getViewport({ scale: 0.5 });
                    const context = canvas.getContext("2d");

                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport,
                    };
                    page.render(renderContext);
                });
            });
        };
        reader.readAsArrayBuffer(file);
    }

    #determinePreviewElement(file_type)
    {
        if (file_type.startsWith("image/"))
            return document.createElement("img");
        else if (file_type.startsWith("video/"))
            return document.createElement("video");
        else if (file_type === "application/pdf")
            return document.createElement("canvas");
        else
            return document.createElement("div");

    }

}