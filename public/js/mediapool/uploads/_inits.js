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
document.addEventListener("DOMContentLoaded", function(event)
{
    const uploaderDialog = new UploaderDialog(
        document.getElementById('uploaderDialog'),
        document.getElementById('openUploadDialog'),
        document.getElementById('closeDialog'),
        document.getElementById("closeUploadDialog")
    );
    const filePreviews = new FilePreviews(
        document.getElementById('dropzone-preview'),
    )
    const dragDropManager = new DragDropManager(
        document.getElementById('dropzone'),
        filePreviews
    );

//    const fileUploader = new FileUploader('#dragDropTab .upload-button', dragDropManager.fileList);



   /*****************************************************************************/
/*
    const dropzone = document.querySelector("#dragDropTab .dropzone");
    const fileList = [];

// Event-Handler für Drag-and-Drop
    dropzone.addEventListener("dragover", (event) => {
        event.preventDefault(); // Standardverhalten unterdrücken
        dropzone.style.borderColor = "#007bff";
    });

    dropzone.addEventListener("dragleave", () => {
        dropzone.style.borderColor = "#ccc"; // Standardfarbe
    });

    dropzone.addEventListener("drop", (event) => {
        event.preventDefault();
        dropzone.style.borderColor = "#ccc";

        const files = event.dataTransfer.files;
        handleFiles(files);
    });

// Dateien verarbeiten
    function handleFiles(files) {
        Array.from(files).forEach((file) => {
            if (file.type.startsWith("image/") || file.type.startsWith("video/") || file.type.startsWith("application/pdf")) {
                fileList.push(file);
                previewFile(file);
            } else {
                alert(`${file.name} wird nicht unterstützt.`);
            }
        });
    }

// Vorschau der Dateien
    function previewFile(file) {
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

// Upload-Button hinzufügen
    const uploadButton = document.querySelector("#dragDropTab .upload-button");
    uploadButton.addEventListener("click", () => {
        if (fileList.length === 0) {
            alert("Keine Dateien zum Hochladen ausgewählt!");
            return;
        }

        const formData = new FormData();
        fileList.forEach((file) => formData.append("files[]", file));

        fetch("/upload", {
            method: "POST",
            body: formData,
        })
            .then((response) => {
                if (response.ok) {
                    alert("Dateien erfolgreich hochgeladen!");
                    fileList.length = 0; // Liste leeren
                    document.querySelector("#dragDropTab .dropzone-preview").innerHTML = ""; // Vorschau leeren
                } else {
                    alert("Fehler beim Hochladen.");
                }
            })
            .catch((error) => console.error("Fehler:", error));
    });

    function renderPDF(file, canvas) {
        const reader = new FileReader();
        reader.onload = function (e) {
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

*/
});

