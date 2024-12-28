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

class FileUploader {
    constructor(uploadButtonSelector, fileList) {
        this.uploadButton = document.querySelector(uploadButtonSelector);
        this.fileList = fileList;

        this.init();
    }

    init()
    {
        this.uploadButton.addEventListener('click', () => this.uploadFiles());
    }

    uploadFiles()
    {
        if (this.fileList.length === 0)
        {
            alert("Keine Dateien zum Hochladen ausgewählt!");
            return;
        }

        const formData = new FormData();
        this.fileList.forEach(file => formData.append("files[]", file));

        fetch("/upload", {
            method: "POST",
            body: formData,
        })
            .then(response => {
                if (response.ok) {
                    alert("Dateien erfolgreich hochgeladen!");
                    this.fileList.length = 0; // Liste leeren
                    document.querySelector("#dragDropTab .dropzone-preview").innerHTML = ""; // Vorschau leeren
                } else {
                    alert("Fehler beim Hochladen.");
                }
            })
            .catch(error => console.error("Fehler:", error));
    }
}