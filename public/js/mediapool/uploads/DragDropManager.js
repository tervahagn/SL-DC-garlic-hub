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

class DragDropManager
{
    constructor(dropzoneSelector, previewContainerSelector, fileList = []) {
        this.dropzone = document.querySelector(dropzoneSelector);
        this.previewContainer = document.querySelector(previewContainerSelector);
        this.fileList = fileList;

        this.init();
    }

    init()
    {
        this.dropzone.addEventListener('dragover', (e) => this.onDragOver(e));
        this.dropzone.addEventListener('dragleave', () => this.onDragLeave());
        this.dropzone.addEventListener('drop', (e) => this.onDrop(e));
    }

    onDragOver(event)
    {
        event.preventDefault();
        this.dropzone.style.borderColor = "#007bff";
    }

    onDragLeave()
    {
        this.dropzone.style.borderColor = "#ccc";
    }

    onDrop(event)
    {
        event.preventDefault();
        this.dropzone.style.borderColor = "#ccc";
        const files = event.dataTransfer.files;
        this.handleFiles(files);
    }

    handleFiles(files)
    {
        Array.from(files).forEach(file => {
            if (file.type.startsWith("image/") || file.type.startsWith("video/") || file.type.startsWith("application/pdf")) {
                this.fileList.push(file);
                this.previewFile(file);
            } else {
                alert(`${file.name} wird nicht unterstützt.`);
            }
        });
    }

    previewFile(file) {
        const previewContainer = document.createElement("div");
        previewContainer.style.margin = "10px";
        previewContainer.style.display = "inline-block";
        previewContainer.style.position = "relative";

        const previewElement = document.createElement(
            file.type.startsWith("image/") ? "img" :
                file.type.startsWith("video/") ? "video" :
                    "div"
        );

        previewElement.style.width = "200px";
        previewElement.style.height = "200px";
        previewElement.style.objectFit = "contain";
        previewElement.style.border = "1px solid #ccc";

        // Schließen-Button hinzufügen
        const closeButton = document.createElement("button");
        closeButton.textContent = "×";
        closeButton.style.position = "absolute";
        closeButton.style.top = "0px";
        closeButton.style.right = "0px";
        closeButton.style.backgroundColor = "#ff4d4d";
        closeButton.style.color = "white";
        closeButton.style.border = "none";
        closeButton.style.borderRadius = "50%";
        closeButton.style.width = "20px";
        closeButton.style.height = "20px";
        closeButton.style.cursor = "pointer";

        closeButton.addEventListener("click", () => {
            const index = fileList.indexOf(file);
            if (index > -1) {
                fileList.splice(index, 1);
            }
            previewContainer.remove();
        });

        previewContainer.appendChild(closeButton);

        // Tooltip hinzufügen
        const tooltip = document.createElement("div");
        tooltip.style.position = "absolute";
        tooltip.style.bottom = "-20px";
        tooltip.style.left = "0";
        tooltip.style.padding = "5px 10px";
        tooltip.style.backgroundColor = "#333";
        tooltip.style.color = "#fff";
        tooltip.style.fontSize = "12px";
        tooltip.style.borderRadius = "4px";
        tooltip.style.visibility = "hidden";
        tooltip.style.whiteSpace = "nowrap";
        tooltip.textContent = `${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
        previewContainer.appendChild(tooltip);

        previewContainer.addEventListener("mouseenter", () => {
            tooltip.style.visibility = "visible";
        });

        previewContainer.addEventListener("mouseleave", () => {
            tooltip.style.visibility = "hidden";
        });

        if (file.type.startsWith("image/") || file.type.startsWith("video/")) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewElement.src = e.target.result;
                if (file.type.startsWith("video/")) previewElement.controls = true;
            };
            reader.readAsDataURL(file);
        } else if (file.type.startsWith("application/pdf")) {
            const canvas = document.createElement("canvas");
            canvas.style.width = "200px";
            canvas.style.height = "200px";
            canvas.style.border = "1px solid #ccc";
            renderPDF(file, canvas);
            previewContainer.appendChild(canvas);
        }

        previewContainer.appendChild(previewElement);

        const dropzonePreview = document.querySelector("#dragDropTab .dropzone-preview");
        dropzonePreview.appendChild(previewContainer);
    }
}